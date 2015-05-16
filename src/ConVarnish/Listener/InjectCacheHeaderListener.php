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
        if (
            $this->varnishOptions->isCacheEnabled() &&
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
        $cacheOptions = $this->getCacheOptions($routeName);
        $headers = $e->getResponse()->getHeaders();

        if ($this->varnishOptions->getDebug()) {
            $debug = new GenericHeader('X-Cache-Debug', '1');
            $headers->addHeader($debug);
        }

        if (false !== $cacheOptions) {
            if (is_array($cacheOptions) && isset($cacheOptions['ttl'])) {
                $this->ttl = (int) $cacheOptions['ttl'];
            } elseif (is_int($cacheOptions)) {
                $this->ttl = $cacheOptions;
            } else {
                $this->ttl = $this->varnishOptions->getDefaultTtl();
            }
        } else {
            $viewModel = $e->getViewModel();
            $esiOptions = (array) $viewModel->getOption('esi', []);
            if (isset($esiOptions['ttl'])) {
                $this->ttl = (int) $esiOptions['ttl'];
            }
        }
        if (!$this->varnishOptions->isCacheEnabled()) {
            $this->ttl = 0;
        }
        $cacheControl = new CacheControl();
        $directives = [
            'no-store' => true,
            'no-cache' => true,
            'must-revalidate' => true,
            'post-check' => 0,
            'pre-check' => 0,
            's-maxage' => $this->ttl,
        ];
        foreach ($directives as $directive => $value) {
            $cacheControl->addDirective($directive, $value);
        }
        $headers->addHeader($cacheControl);
    }

    /**
     *
     *
     * @param string $routeName
     * @return array|false array options or false if nothing found
     */
    private function getCacheOptions($routeName)
    {
        $cacheableRoutes = $this->varnishOptions->getCacheableRoutes();
        foreach ($cacheableRoutes as $pattern => $cacheableRoute) {
            if (fnmatch($pattern, $routeName)) {
                return $cacheableRoute;
            }
        }
        return false;
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function determineEsiProcessing(MvcEvent $e)
    {
        $requestHeaders = $e->getRequest()->getHeaders();
        $this->responseHeaders = $e->getResponse()->getHeaders();
        if (
            ($this->ttl > 0) &&
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
        if ($block->getOption('esi')) {
            $block->setTemplate('con-varnish/esi');
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
    private function canUseEsi()
    {
        return (bool) $this->canUseEsi;
    }
}
