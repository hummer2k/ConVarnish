<?php

namespace ConVarnishTest;

use PHPUnit\Framework\TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @var MvcEvent;
     */
    protected $mvcEvent;

    protected function createMvcEvent()
    {
        $this->mvcEvent = new MvcEvent();
        $request = new Request();
        $response = new Response();
        $routeMatch = new \Zend\Router\Http\RouteMatch([]);
        $routeMatch->setMatchedRouteName('test/route');
        $routeMatch->setParam('controller', 'Application\Controller\Index');
        $routeMatch->setParam('action', 'index');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $this->mvcEvent->setRequest($request);
        $this->mvcEvent->setResponse($response);
    }
}
