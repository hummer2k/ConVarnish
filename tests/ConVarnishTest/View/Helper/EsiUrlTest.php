<?php

namespace ConVarnishTest\View\Helper;

use ConVarnish\View\Helper\EsiUrl;
use ConVarnish\View\Helper\EsiUrlFactory;
use ConVarnishTest\AbstractTest;
use ConVarnishTest\Bootstrap;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiUrlTest extends AbstractTest
{
    public function testFactory()
    {
        $factory = new EsiUrlFactory();
        $serviceManager = new ServiceManager();
        $instance = $factory->createService($serviceManager);
        $this->assertInstanceOf('ConVarnish\View\Helper\EsiUrl', $instance);
    }

    public function testInvoke()
    {
        $renderer = new PhpRenderer();
        /* @var $url Url */
        $url = $renderer->plugin('url');
        $url->setRouter(Bootstrap::getServiceManager()->get('HttpRouter'));
        $esiUrl = new EsiUrl();
        $esiUrl->setView($renderer);

        $this->assertEquals('/esi/my-block', call_user_func($esiUrl, 'my-block'));

        $this->assertEquals(
            '/esi/my-block?handles%5B0%5D=handle1&handles%5B1%5D=handle2',
            call_user_func($esiUrl, 'my-block', ['handle1', 'handle2'])
        );
    }
}
