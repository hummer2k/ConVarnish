<?php

use ConVarnish\Listener\InjectCacheHeaderListener;
use ConVarnish\Listener\InjectCacheHeaderListenerFactory;
use ConVarnish\Listener\InjectTagsHeaderListener;
use ConVarnish\Options\VarnishOptions;
use ConVarnish\Options\VarnishOptionsFactory;
use ConVarnish\Service\VarnishService;
use ConVarnish\Service\VarnishServiceFactory;
use ConVarnish\Service\VarnishServiceInterface;
use ConVarnish\Strategy\ActionStrategy;
use ConVarnish\Strategy\CachingStrategyFactory;
use ConVarnish\Strategy\DefaultStrategy;
use ConVarnish\Strategy\EsiStrategy;
use ConVarnish\Strategy\RouteStrategy;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'factories' => [
        InjectCacheHeaderListener::class => InjectCacheHeaderListenerFactory::class,
        VarnishService::class => VarnishServiceFactory::class,
        VarnishOptions::class => VarnishOptionsFactory::class,
        DefaultStrategy::class => CachingStrategyFactory::class,
        ActionStrategy::class => CachingStrategyFactory::class,
        RouteStrategy::class => CachingStrategyFactory::class,
        EsiStrategy::class => CachingStrategyFactory::class,
        InjectTagsHeaderListener::class => InvokableFactory::class
    ],
    'aliases' => [
        VarnishServiceInterface::class => VarnishService::class
    ]
];
