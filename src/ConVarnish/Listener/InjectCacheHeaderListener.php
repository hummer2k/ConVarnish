<?php
/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Listener;

use ConLayout\Block\Factory\BlockFactory;
use ConLayout\Updater\LayoutUpdaterInterface;
use ConVarnish\Options\VarnishOptions;
use ConVarnish\Strategy\CachingStrategyInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Http\Header\CacheControl;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Headers;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

class InjectCacheHeaderListener implements
    ListenerAggregateInterface,
    EventManagerAwareInterface
{
    const HEADER_CACHE_DEBUG = 'X-Cache-Debug';
    const ESI_TEMPLATE = 'con-varnish/esi';
    const EVENT_DETERMINE_TTL = 'determine_ttl';

    use ListenerAggregateTrait;
    use EventManagerAwareTrait;

    /**
     *
     * @var LayoutUpdaterInterface
     */
    private $layoutUpdater;

    /**
     *
     * @var VarnishOptions
     */
    private $varnishOptions;

        /**
     *
     * @var bool
     */
    private $canUseEsi = false;

    /**
     *
     * @var Headers
     */
    private $responseHeaders;

    /**
     *
     * @var bool
     */
    private $esiHeaderInjected = false;

    /**
     *
     * @var int
     */
    private $ttl = 0;


    /**
     *
     * @param VarnishOptions $varnishOptions
     */
    public function __construct(VarnishOptions $varnishOptions)
    {
        $this->varnishOptions = $varnishOptions;
    }

    /**
     * @return LayoutUpdaterInterface
     */
    public function getLayoutUpdater()
    {
        return $this->layoutUpdater;
    }

    /**
     * @param LayoutUpdaterInterface $layoutUpdater
     */
    public function setLayoutUpdater(LayoutUpdaterInterface $layoutUpdater)
    {
        $this->layoutUpdater = $layoutUpdater;
    }

    /**
     *
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH,
            [$this, 'injectCacheHeader'],
            -10
        );
        if ($this->varnishOptions->isCacheEnabled() &&
            $this->varnishOptions->useEsi()
        ) {
            $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'determineEsiProcessing'], -15);
            $events->getSharedManager()->attach(
                BlockFactory::class,
                'configure.post',
                [$this, 'injectEsi']
            );
        }
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function injectCacheHeader(MvcEvent $e)
    {
        $cacheEvent = clone $e;
        $cacheEvent->setTarget($this);
        $cacheEvent->setName(self::EVENT_DETERMINE_TTL);

        if ($this->varnishOptions->isCacheEnabled()) {
            $result = $this->getEventManager()->trigger(
                $cacheEvent,
                function ($result) {
                    return $result instanceof CachingStrategyInterface;
                }
            );
            /** @var CachingStrategyInterface $strategy */
            $strategy = $result->last();
            $ttl = $strategy->getTtl();
        } else {
            $ttl = 0;
        }

        $headers = $e->getResponse()->getHeaders();

        if ($this->varnishOptions->getDebug()) {
            $debugValue = isset($strategy) ? get_class($strategy) : 'caching disabled';
            $debug = new GenericHeader(self::HEADER_CACHE_DEBUG, $debugValue);
            $headers->addHeader($debug);
        }

        $cacheControl = new CacheControl();
        $directives = [
            'no-store' => true,
            'no-cache' => true,
            'must-revalidate' => true,
            'post-check' => 0,
            'pre-check' => 0,
            's-maxage' => $ttl,
        ];
        foreach ($directives as $directive => $value) {
            $cacheControl->addDirective($directive, $value);
        }
        $headers->addHeader($cacheControl);

        $this->setTtl($ttl);
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function determineEsiProcessing(MvcEvent $e)
    {
        $requestHeaders = $e->getRequest()->getHeaders();
        $this->responseHeaders = $e->getResponse()->getHeaders();
        if (($this->ttl > 0) &&
            $requestHeaders->has('Surrogate-Capability') &&
            false !== strpos($requestHeaders->get('Surrogate-Capability')->getFieldValue(), 'ESI/1.0') &&
            $e->getRouteMatch()->getMatchedRouteName() !== 'esi'
        ) {
            $this->canUseEsi = true;
        }
    }

    /**
     *
     * @param EventInterface $e
     */
    public function injectEsi(EventInterface $e)
    {
        if (!$this->canUseEsi()) {
            return;
        }
        /* @var $block ViewModel */
        $block = $e->getParam('block');
        if ($options = $block->getOption('esi')) {
            $block->setTemplate(self::ESI_TEMPLATE);
            $block->setVariable('__HANDLES__', $this->getHandles($block));
            if ($this->varnishOptions->getDebug()) {
                $block->setVariables([
                    '__DEBUG__' => true,
                    '__TTL__'   => isset($options['ttl']) ? $options['ttl'] : 'n/a',
                    '__TAGS__'  => $block->getOption('cache_tags', [])
                ]);
            }
            $this->injectEsiHeader();
        }
    }

    /**
     *
     * @param ViewModel $block
     * @return array
     */
    private function getHandles(ViewModel $block)
    {
        $options = $block->getOption('esi');
        $optionHandles  = isset($options['handles'])
            ? (array) $options['handles']
            : [];
        $currentHandles = $this->layoutUpdater->getHandles(true);
        $handlesIndex = [];
        foreach ($currentHandles as $currentHandle) {
            $handlesIndex[$currentHandle->getName()] = $currentHandle->getPriority();
        }
        $handles = [];
        foreach ($optionHandles as $optionHandle) {
            $priority = isset($handlesIndex[$optionHandle])
                ? $handlesIndex[$optionHandle]
                : 1;
            $handles[$optionHandle] = $priority;
        }
        return $handles;
    }

    /**
     *
     */
    private function injectEsiHeader()
    {
        if (!$this->esiHeaderInjected) {
            $this->responseHeaders->addHeaderLine('Surrogate-Control', 'ESI/1.0');
            $this->esiHeaderInjected = true;
        }
    }

    /**
     *
     * @return bool
     */
    public function canUseEsi()
    {
        return (bool) $this->canUseEsi && $this->layoutUpdater;
    }

    /**
     *
     * @param bool $canUseEsi
     * @return InjectCacheHeaderListener
     */
    public function setCanUseEsi($canUseEsi)
    {
        $this->canUseEsi = $canUseEsi;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     *
     * @param int $ttl
     * @return InjectCacheHeaderListener
     */
    public function setTtl($ttl)
    {
        $this->ttl = (int) $ttl;
        return $this;
    }
}
