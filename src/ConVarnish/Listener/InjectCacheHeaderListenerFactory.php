<?php
namespace ConVarnish\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectCacheHeaderListenerFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return InjectCacheHeaderListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $varnishOptions = $serviceLocator->get('ConVarnish\Options\VarnishOptions');
        $routeListener = new InjectCacheHeaderListener($varnishOptions);
        return $routeListener;
    }
}
