<?php
namespace ConVarnish\Listener;

use ConVarnish\Options\VarnishOptions,
    Zend\EventManager\EventManagerInterface,
    Zend\EventManager\ListenerAggregateInterface,
    Zend\EventManager\ListenerAggregateTrait,
    Zend\Http\Header\CacheControl,
    Zend\Http\Header\GenericHeader,
    Zend\Mvc\MvcEvent;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RouteListener
    implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
        
    /**
     *
     * @var VarnishOptions
     */
    protected $varnishOptions;
    
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
        if ($this->varnishOptions->isCacheEnabled()) {
            $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'injectEsiHeader'));
            $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'injectCacheHeader'));
        }
    }
    
    /**
     * 
     * @param MvcEvent $e
     * @return void
     */
    public function injectEsiHeader(MvcEvent $e)
    {
        $headers = $e->getRequest()->getHeaders();
        if (
            !$headers->has('surrogate-capability')
            || false === strpos($headers->get('surrogate-capability')->getFieldValue(), 'ESI/1.0')
        ) {
            // No esi processing
            return;
        }
    }
    
    /**
     * 
     * @param MvcEvent $e
     * @return RouteListener
     */
    public function injectCacheHeader(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $routeName = $routeMatch->getMatchedRouteName();
        $cacheOptions = $this->getCacheOptions($routeName);
        
        if (false === $cacheOptions) {
            return;            
        }
        
        $ttl = isset($cacheOptions['ttl']) 
            ? $cacheOptions['ttl']
            : $this->varnishOptions->getDefaultTtl();
        $headers = $e->getResponse()->getHeaders();
        
        $cacheControl = new CacheControl();
        $cacheControl->addDirective('s-maxage', $ttl);
        $headers->addHeader($cacheControl);
        
        if ($this->varnishOptions->getDebug()) {
            $debug = new GenericHeader('X-Cache-Debug', '1');
            $headers->addHeader($debug);
        }
        
        return $this;
    }
    
    /**
     * 
     * 
     * @param string $routeName
     * @return array|false array options or false if nothing found
     */
    protected function getCacheOptions($routeName)
    {
        $cacheableRoutes = $this->varnishOptions->getCacheableRoutes();
        return isset($cacheableRoutes[$routeName])
            ? $cacheableRoutes[$routeName]
            : false;
    }
        
}
