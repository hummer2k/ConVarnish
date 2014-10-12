<?php
namespace ConVarnish\Listener;

use Zend\EventManager\ListenerAggregateInterface;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class RouteListener
    implements ListenerAggregateInterface
{
    use \Zend\EventManager\ListenerAggregateTrait;
    
    public function attach(\Zend\EventManager\EventManagerInterface $events)
    {
        
    }
}
