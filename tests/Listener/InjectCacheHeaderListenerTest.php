<?php

namespace ConVarnishTest\Listener;

use ConLayout\Updater\LayoutUpdater;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConVarnish\Listener\InjectCacheHeaderListener;
use ConVarnish\Options\VarnishOptions;
use ConVarnish\Strategy\AbstractCachingStrategy;
use ConVarnish\Strategy\ActionStrategy;
use ConVarnish\Strategy\DefaultStrategy;
use ConVarnish\Strategy\EsiStrategy;
use ConVarnish\Strategy\RouteStrategy;
use ConVarnishTest\AbstractTest;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectCacheHeaderListenerTest extends AbstractTest
{
    /**
     *
     * @var InjectCacheHeaderListener
     */
    protected $listener;

    /**
     *
     * @var VarnishOptions
     */
    protected $varnishOptions;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    protected $layoutUpdater;

    /**
     *
     * @var EventManager
     */
    protected $eventManager;

    protected function setUp(): void
    {
        $this->varnishOptions = new VarnishOptions();
        $this->varnishOptions->setCacheEnabled(true);
        $this->listener = new InjectCacheHeaderListener($this->varnishOptions);
        $this->listener->setLayoutUpdater(new LayoutUpdater());

        $this->eventManager = new EventManager(new SharedEventManager());
        $this->eventManager->getSharedManager()->clearListeners(
            InjectCacheHeaderListener::class
        );
        $this->attachStrategy(DefaultStrategy::class, 100);
        $this->listener->setEventManager($this->eventManager);
        $this->createMvcEvent();
    }

    public function testInjectCacheHeaderDefault()
    {
        $this->varnishOptions->setPolicy(VarnishOptions::POLICY_ALLOW);
        $this->varnishOptions->setDefaultTtl(1234);

        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(
            $this->varnishOptions->getDefaultTtl(),
            $this->listener->getTtl()
        );

        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(
            $this->varnishOptions->getDefaultTtl(),
            $this->listener->getTtl()
        );

        $this->assertTrue(
            $this->mvcEvent->getResponse()->getHeaders()->has('Cache-Control')
        );
    }

    public function testInjectCacheHeaderActionTtlWithPattern()
    {
        $this->attachStrategy(ActionStrategy::class);

        $this->varnishOptions->setCacheableActions([
            'Application\Controller\Index*' => 9876
        ]);

        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(
            9876,
            $this->listener->getTtl()
        );
    }

    public function testInjectCacheHeaderActionTtlIndex()
    {
        $this->attachStrategy(ActionStrategy::class);
        $this->varnishOptions->setCacheableActions([
            'Application\Controller\Index::index' => 5432
        ]);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(
            5432,
            $this->listener->getTtl()
        );
    }

    public function testInjectCacheHeaderRouteTtlWithPattern()
    {
        $this->attachStrategy(RouteStrategy::class);
        $this->varnishOptions->setCacheableRoutes(['test/*' => 3600]);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(3600, $this->listener->getTtl());
    }

    public function testInjectCacheHeaderRouteTtlIndex()
    {
        $this->attachStrategy(RouteStrategy::class);
        $this->varnishOptions->setCacheableRoutes([
            'test/route' => 1200
        ]);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(1200, $this->listener->getTtl());
    }

    public function testEnableDebug()
    {
        $this->varnishOptions->setDebug(true);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $headers = $this->mvcEvent->getResponse()->getHeaders();
        $this->assertTrue($headers->has(InjectCacheHeaderListener::HEADER_CACHE_DEBUG));
    }

    public function testInjectCacheHeaderWithEsiViewModel()
    {
        $this->attachStrategy(EsiStrategy::class);
        $viewModel = new ViewModel();
        $viewModel->setOption('esi', ['ttl' => 600]);
        $this->mvcEvent->setViewModel($viewModel);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(600, $this->listener->getTtl());
    }

    public function testCanUseEsi()
    {
        $this->attachStrategy(EsiStrategy::class);

        $this->listener->determineEsiProcessing($this->mvcEvent);
        $this->assertFalse($this->listener->canUseEsi());

        $reqHeaders = $this->mvcEvent->getRequest()->getHeaders();
        $reqHeaders->addHeaderLine('Surrogate-Capability', 'varnish=ESI/1.0');
        $this->listener->setTtl(60);
        $this->listener->determineEsiProcessing($this->mvcEvent);

        $this->assertTrue($this->listener->canUseEsi());
    }

    public function testInjectEsi()
    {
        $block = new ViewModel();
        $block->setOption('esi', ['ttl' => 120]);
        $block->setTemplate('default');
        $event = new Event();
        $event->setParam('block', $block);

        $this->listener->determineEsiProcessing($this->mvcEvent);

        $this->listener->injectEsi($event);
        $this->assertEquals('default', $block->getTemplate());

        $this->listener->setCanUseEsi(true);
        $this->listener->injectEsi($event);
        $this->assertEquals(
            InjectCacheHeaderListener::ESI_TEMPLATE,
            $block->getTemplate()
        );
    }

    public function testStrategyPriorities()
    {
        $this->attachStrategy(RouteStrategy::class, 200);
        $this->attachStrategy(ActionStrategy::class, 600);

        $this->varnishOptions->setCacheableActions([
            'Application\Controller\Index*' => 120
        ]);
        $this->varnishOptions->setCacheableRoutes([
            'test/route' => 360
        ]);

        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(120, $this->listener->getTtl());
    }

    public function testStrategyWithoutMatch()
    {
        $this->attachStrategy(RouteStrategy::class, 200);
        $this->attachStrategy(ActionStrategy::class, 600);

        $this->varnishOptions->setCacheableActions([
            'No\Match*' => 120
        ]);
        $this->varnishOptions->setCacheableRoutes([
            'test/route' => 360
        ]);

        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(360, $this->listener->getTtl());
    }

    protected function attachStrategy($class, $priority = 500)
    {
        /** @var AbstractCachingStrategy $strategy */
        $strategy = new $class($this->varnishOptions);
        $strategy->attach($this->eventManager, $priority);
    }
}
