<?php
namespace ConVarnish;

use Zend\Mvc\Application,
    Zend\Mvc\MvcEvent,
    Zend\View\Model\ViewModel;

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
    
    public function onBootstrap(MvcEvent $e)
    {
        /* @var $application Application */
        $application = $e->getApplication();
        $eventManager = $application->getEventManager()->getSharedManager();
        
        $eventManager->attach('ConLayout\View\Renderer\BlockRenderer', 'render.pre', function($e) {
            /* @var $viewModel ViewModel */
            $viewModel = $e->getParam('viewModel');
            if (null !== $viewModel->getOption('esi')) {
                $viewModel->setTemplate('esi');
            }
        });
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
