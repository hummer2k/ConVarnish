<?php

namespace ConVarnishTest;

use PHPUnit\Framework\TestCase;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\MvcEvent;

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
        $routeMatch = new \Laminas\Router\Http\RouteMatch([]);
        $routeMatch->setMatchedRouteName('test/route');
        $routeMatch->setParam('controller', 'Application\Controller\Index');
        $routeMatch->setParam('action', 'index');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $this->mvcEvent->setRequest($request);
        $this->mvcEvent->setResponse($response);
    }
}
