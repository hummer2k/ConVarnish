<?php

return [
    'factories' => [
        \ConVarnish\Listener\InjectCacheHeaderListener::class
            => \ConVarnish\Listener\InjectCacheHeaderListenerFactory::class,
        \ConVarnish\Service\VarnishService::class
            => \ConVarnish\Service\VarnishServiceFactory::class,
        \ConVarnish\Options\VarnishOptions::class
            => \ConVarnish\Options\VarnishOptionsFactory::class,
        \ConVarnish\Strategy\DefaultStrategy::class
            => \ConVarnish\Strategy\CachingStrategyFactory::class,
        \ConVarnish\Strategy\ActionStrategy::class
            => \ConVarnish\Strategy\CachingStrategyFactory::class,
        \ConVarnish\Strategy\RouteStrategy::class
            => \ConVarnish\Strategy\CachingStrategyFactory::class,
        \ConVarnish\Strategy\EsiStrategy::class
            => \ConVarnish\Strategy\CachingStrategyFactory::class
    ],
    'invokables' => [
        \ConVarnish\Listener\InjectTagsHeaderListener::class
            => \ConVarnish\Listener\InjectTagsHeaderListener::class
    ],
    'aliases' => [
        \ConVarnish\Service\VarnishServiceInterface::class
            => \ConVarnish\Service\VarnishService::class
    ]
];
