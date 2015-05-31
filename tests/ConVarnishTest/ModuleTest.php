<?php

namespace ConVarnishTest;

use ConVarnish\Module;
use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ModuleTest extends AbstractTest
{
    protected $module;

    public function setUp()
    {
        $this->module = new Module();
    }

    public function testGetConfigs()
    {
        $this->assertInternalType('array', $this->module->getAutoloaderConfig());
        $this->assertInternalType('array', $this->module->getConfig());
        $this->assertInternalType('array', $this->module->getServiceConfig());
    }

    public function testOnBootstrap()
    {
        $event = new MvcEvent();
        $application = new Application([], Bootstrap::getServiceManager());
        $em = new EventManager();
        $application->setEventManager($em);
        $event->setApplication($application);
        $this->module->onBootstrap($event);

        $this->assertCount(1, $em->getListeners(MvcEvent::EVENT_DISPATCH));
        $this->assertCount(1, $em->getListeners(MvcEvent::EVENT_RENDER));
    }
}
