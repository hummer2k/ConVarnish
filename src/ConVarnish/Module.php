<?php
namespace ConVarnish;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * retrieve module config
     *
     * @return array
     */
    public function getConfig()
    {
        return array_merge(
            include __DIR__ . '/../../config/module.config.php',
            include __DIR__ . '/../../config/con-varnish.global.php.dist'
        );
    }

    /**
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/service.config.php';
    }

    /**
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        /* @var $application Application */
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager = $application->getEventManager();

        $listeners = [
            'ConVarnish\Listener\InjectCacheHeaderListener',
            'ConVarnish\Listener\InjectTagsHeaderListener'
        ];

        foreach ($listeners as $listener) {
            $eventManager->attach($serviceManager->get($listener));
        }
    }

    /**
     * retrieve autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/../'. __NAMESPACE__,
                ],
            ],
        ];
    }
}
