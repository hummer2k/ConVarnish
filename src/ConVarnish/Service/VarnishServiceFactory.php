<?php
namespace ConVarnish\Service;

use Zend\ServiceManager\FactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishServiceFactory
    implements FactoryInterface
{
    /**
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return VarnishService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $varnishService = new VarnishService();
        return $varnishService;
    }
}
