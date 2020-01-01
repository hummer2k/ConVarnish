<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\Service;

use ConVarnish\Options\VarnishOptions;
use Psr\Container\ContainerInterface;

class VarnishServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VarnishService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get(VarnishOptions::class);
        return new VarnishService($options->getServers());
    }
}
