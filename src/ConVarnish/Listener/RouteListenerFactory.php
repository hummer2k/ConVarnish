<?php
namespace ConVarnish\Listener;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RouteListenerFactory
    implements FactoryInterface
{
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return RouteListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $varnishOptions = $serviceLocator->get('ConVarnish\Options\VarnishOptions');
        $routeListener = new RouteListener($varnishOptions);        
        return $routeListener;
    }
}
