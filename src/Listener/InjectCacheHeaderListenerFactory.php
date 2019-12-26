<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Listener;

use ConLayout\Updater\LayoutUpdaterInterface;
use ConVarnish\Options\VarnishOptions;
use Psr\Container\ContainerInterface;

class InjectCacheHeaderListenerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var VarnishOptions $varnishOptions */
        $varnishOptions = $container->get(VarnishOptions::class);
        $routeListener = new InjectCacheHeaderListener($varnishOptions);
        if ($container->has(LayoutUpdaterInterface::class)) {
            $routeListener->setLayoutUpdater($container->get(LayoutUpdaterInterface::class));
        }
        return $routeListener;
    }
}
