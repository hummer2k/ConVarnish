<?php

namespace ConVarnishTest\Listener;

use ConLayout\Updater\LayoutUpdaterInterface;
use ConVarnish\Listener\InjectCacheHeaderListener;
use ConVarnish\Options\VarnishOptions;
use ConVarnishTest\AbstractTest;
use ConVarnishTest\Bootstrap;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
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

    public function setUp()
    {
        $this->varnishOptions = Bootstrap::getServiceManager()
            ->create('ConVarnish\Options\VarnishOptions');
        $this->layoutUpdater = Bootstrap::getServiceManager()
            ->create('ConLayout\Updater\LayoutUpdaterInterface');
        $this->listener = new InjectCacheHeaderListener(
            $this->varnishOptions,
            $this->layoutUpdater
        );
        $this->eventManager = new EventManager();
        $this->createMvcEvent();
    }

    public function testAttachDefault()
    {
        $this->listener->attach($this->eventManager);
        $listeners = $this->eventManager->getListeners(MvcEvent::EVENT_DISPATCH);
        $this->assertCount(1, $listeners);
    }

    public function testAttachEnabled()
    {
        $this->varnishOptions->setCacheEnabled(true);
        $this->varnishOptions->setUseEsi(true);

        $this->listener->attach($this->eventManager);
        $listeners = $this->eventManager->getListeners(MvcEvent::EVENT_DISPATCH);
        $this->assertCount(2, $listeners);

        $sharedListeners = $this->eventManager->getSharedManager()->getListeners(
            'ConLayout\Block\Factory\BlockFactory',
            'createBlock.post'
        );

        $this->assertCount(1, $sharedListeners);
    }

    public function testInjectCacheHeaderDefault()
    {
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(
            0,
            $this->listener->getTtl()
        );

        $this->varnishOptions->setCacheEnabled(true);
        $this->listener->injectCacheHeader($this->mvcEvent);
        $this->assertEquals(
            $this->varnishOptions->getDefaultTtl(),
            $this->listener->getTtl()
        );

        $this->assertTrue(
            $this->mvcEvent->getResponse()->getHeaders()->has('Cache-Control')
        );
    }

    public function testInjectCacheHeaderDisabledRoute()
    {
        $this->varnishOptions->setCacheEnabled(true);
        $this->varnishOptions->setUncacheableRoutes(['test/*']);
        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(0, $this->listener->getTtl());
    }

    public function testInjectCacheHeaderRouteTtl()
    {
        $this->varnishOptions->setCacheEnabled(true);
        $this->varnishOptions->setCacheableRoutes(['test/*' => 3600]);
        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(3600, $this->listener->getTtl());
    }

    public function testEnableDebug()
    {
        $this->varnishOptions->setCacheEnabled(true);
        $this->varnishOptions->setDebug(true);
        $this->listener->injectCacheHeader($this->mvcEvent);

        $headers = $this->mvcEvent->getResponse()->getHeaders();
        $this->assertTrue($headers->has(InjectCacheHeaderListener::HEADER_CACHE_DEBUG));
    }

    public function testInjectCacheHeaderWithEsiViewModel()
    {
        $this->varnishOptions->setCacheEnabled(true);
        $viewModel = new ViewModel();
        $viewModel->setOption('esi', ['ttl' => 600]);
        $this->mvcEvent->setViewModel($viewModel);
        $this->listener->injectCacheHeader($this->mvcEvent);

        $this->assertEquals(600, $this->listener->getTtl());
    }

    public function testCanUseEsi()
    {
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
}
