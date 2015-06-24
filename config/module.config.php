<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'ConVarnish\View\Helper\EsiUrl' => 'ConVarnish\View\Helper\EsiUrl'
        ],
        'aliases' => [
            'esiUrl' => 'ConVarnish\View\Helper\EsiUrl'
        ]
    ],
    'controllers' => [
        'invokables' => [
            'ConVarnish\Controller\Esi' => 'ConVarnish\Controller\EsiController'
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
                        'controller' => 'ConVarnish\Controller\Esi',
                        'action' => 'block'
                    ]
                ]
            ]
        ]
    ]
];
