<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Options;

use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class VarnishOptionsFactory
{
    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return VarnishOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, VarnishOptions::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VarnishOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $varnishConfig = isset($config['varnish'])
            ? (array) $config['varnish']
            : array();
        return new VarnishOptions($varnishConfig);
    }
}
