<?php
namespace ConVarnish;

class Module
{
    /**
     * retrieve module config
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * retrieve autoloader config
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../'. __NAMESPACE__,
                ),
            ),
        );
    }
}
