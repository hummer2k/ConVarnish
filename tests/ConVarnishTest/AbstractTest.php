<?php

namespace ConVarnishTest;

use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\MvcEvent;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
abstract class AbstractTest extends PHPUnit_Framework_TestCase
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
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('test/route');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $this->mvcEvent->setRequest($request);
        $this->mvcEvent->setResponse($response);
    }

}