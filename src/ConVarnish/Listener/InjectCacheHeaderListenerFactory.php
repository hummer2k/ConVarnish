<?php
namespace ConVarnish\Listener;

use ConLayout\Updater\LayoutUpdaterInterface;
use ConVarnish\Options\VarnishOptions;
use Interop\Container\ContainerInterface;
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
        return $this($serviceLocator, InjectCacheHeaderListener::class);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var VarnishOptions $varnishOptions */
        $varnishOptions = $container->get(VarnishOptions::class);
        $routeListener = new InjectCacheHeaderListener($varnishOptions);
        if ($container->has('ConLayout\Updater\LayoutUpdaterInterface')) {
            $routeListener->setLayoutUpdater($container->get(LayoutUpdaterInterface::class));
        }
        return $routeListener;
    }
}
