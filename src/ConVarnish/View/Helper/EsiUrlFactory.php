<?php

namespace ConVarnish\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiUrlFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();
        $handles = new EsiUrl(
            $serviceManager->get('ConLayout\Updater\LayoutUpdaterInterface')
        );
        return $handles;
    }
}
