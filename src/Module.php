<?php

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish;

use ConVarnish\Listener\InjectCacheHeaderListener;
use ConVarnish\Listener\InjectTagsHeaderListener;
use ConVarnish\Options\VarnishOptions;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use Laminas\Mvc\Application;

class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    BootstrapListenerInterface
{
    /**
     * retrieve module config
     *
     * @return array
     */
    public function getConfig()
    {
        return array_merge(
            include __DIR__ . '/../config/module.config.php',
            include __DIR__ . '/../config/con-varnish.global.php.dist'
        );
    }

    /**
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../config/service.config.php';
    }

    /**
     * @param EventInterface $e
     * @return array|void
     */
    public function onBootstrap(EventInterface $e)
    {
        if (PHP_SAPI == 'cli') {
            return;
        }
        /* @var $application Application */
        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();
        /** @var VarnishOptions $varnishOptions */
        $varnishOptions = $serviceManager->get(VarnishOptions::class);

        $listeners = [
            InjectCacheHeaderListener::class,
            InjectTagsHeaderListener::class
        ];

        foreach ($listeners as $listener) {
            $serviceManager->get($listener)->attach($eventManager);
        }

        $cachingStrategies = $varnishOptions->getCachingStrategies();
        foreach ($cachingStrategies as $cachingStrategy => $priority) {
            $serviceManager->get($cachingStrategy)->attach($eventManager, $priority);
        }
    }
}
