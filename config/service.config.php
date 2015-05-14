<?php

return [
    'factories' => [
        'ConVarnish\Listener\InjectCacheHeaderListener'
            => 'ConVarnish\Listener\InjectCacheHeaderListenerFactory',
        'ConVarnish\Options\VarnishOptions'
            => 'ConVarnish\Options\VarnishOptionsFactory',
        'ConVarnish\Service\VarnishService'
            => 'ConVarnish\Service\VarnishServiceFactory',
    ],
    'invokables' => [
        'ConVarnish\Listener\InjectTagsHeaderListener'
            => 'ConVarnish\Listener\InjectTagsHeaderListener'
    ]
];
