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
        $layoutUpdater  = $serviceLocator->get('ConLayout\Updater\LayoutUpdaterInterface');
        $routeListener = new InjectCacheHeaderListener(
            $varnishOptions,
            $layoutUpdater
        );
        return $routeListener;
    }
}
