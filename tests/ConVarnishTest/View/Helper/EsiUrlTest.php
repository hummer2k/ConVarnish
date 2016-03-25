<?php
namespace ConVarnishTest\View\Helper;

use ConVarnish\View\Helper\EsiUrl;
use ConVarnish\View\Helper\EsiUrlFactory;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\View;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new EsiUrlFactory();
        $helperManager = new HelperPluginManager();
        $helperManager->setServiceLocator(new ServiceManager());

        $this->assertInstanceOf(
            EsiUrl::class,
            $factory->createService($helperManager)
        );

    }
}
