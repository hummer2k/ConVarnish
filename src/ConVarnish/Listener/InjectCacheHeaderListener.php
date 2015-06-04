<?php
namespace ConVarnish\Listener;

use ConVarnish\Options\VarnishOptions;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Http\Header\CacheControl;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Headers;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectCacheHeaderListener implements ListenerAggregateInterface
{
    const HEADER_CACHE_DEBUG = 'X-Cache-Debug';
    const ESI_TEMPLATE = 'con-varnish/esi';

    use ListenerAggregateTrait;

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
     * @param array $varnishOptions
     */
    public function __construct(VarnishOptions $varnishOptions)
    {
        $this->varnishOptions = $varnishOptions;
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
                'ConLayout\Block\Factory\BlockFactory',
                'createBlock.post',
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
        $routeMatch = $e->getRouteMatch();
        $routeName = $routeMatch->getMatchedRouteName();

        if (!$this->canCacheRoute($routeName)) {
            $ttl = 0;
        } else {
            $ttl = $this->getTtlForRoute($routeName);
        }

        $headers = $e->getResponse()->getHeaders();

        if ($this->varnishOptions->getDebug()) {
            $debug = new GenericHeader(self::HEADER_CACHE_DEBUG, '1');
            $headers->addHeader($debug);
        }

        $viewModel = $e->getViewModel();
        $esiOptions = (array) $viewModel->getOption('esi', []);
        if (isset($esiOptions['ttl'])) {
            $ttl = (int) $esiOptions['ttl'];
        }

        if (!$this->varnishOptions->isCacheEnabled()) {
            $ttl = 0;
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

        $this->ttl = $ttl;
    }

    /**
     *
     * @param string $routeName
     * @return int
     */
    private function getTtlForRoute($routeName)
    {
        $cacheableRoutes = $this->varnishOptions->getCacheableRoutes();
        foreach ($cacheableRoutes as $pattern => $ttl) {
            if (fnmatch($pattern, $routeName)) {
                return (int) $ttl;
            }
        }
        return $this->varnishOptions->getDefaultTtl();
    }

    /**
     *
     * @param string $routeName
     * @return boolean
     */
    private function canCacheRoute($routeName)
    {
        $uncacheableRoutes = $this->varnishOptions->getUncacheableRoutes();
        foreach ($uncacheableRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return false;
            }
        }
        return true;
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
            $handles = isset($options['handles']) ? (array) $options['handles'] : [];
            $block->setVariable('__HANDLES__', $handles);
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
        return (bool) $this->canUseEsi;
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
