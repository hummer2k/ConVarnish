<?php

use ConVarnish\Controller\EsiController;
use ConVarnish\View\Helper\EsiUrl;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            EsiUrl::class => EsiUrl::class
        ],
        'aliases' => [
            'esiUrl' => EsiUrl::class
        ]
    ],
    'controllers' => [
        'factories' => [
            EsiController::class => InvokableFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'esi' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/esi/:block',
                    'constraints' => [
                        'block' => '[A-Za-z0-9_.-]+'
                    ],
                    'defaults' => [
                        'controller' => EsiController::class,
                        'action' => 'block'
                    ]
                ]
            ]
        ]
    ]
];
