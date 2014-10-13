<?php

return array(
    'factories' => array(
        'ConVarnish\Listener\RouteListener' => 'ConVarnish\Listener\RouteListenerFactory',
        'ConVarnish\Options\VarnishOptions' => 'ConVarnish\Options\VarnishOptionsFactory',
        'ConVarnish\Service\VarnishService' => 'ConVarnish\Service\VarnishServiceFactory',
    )
);