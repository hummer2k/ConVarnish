<?php
namespace ConVarnishTest\Controller;

use ConVarnish\Controller\EsiController;
use ConVarnish\Controller\EsiControllerFactory;
use ConVarnishTest\AbstractTest;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiControllerTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new EsiControllerFactory();
        $controllerManager = new ControllerManager(new ServiceManager());

        $this->assertInstanceOf(
            EsiController::class,
            $factory->createService($controllerManager)
        );

    }
}
