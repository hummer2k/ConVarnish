<?php
return [
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'ConVarnish\View\Helper\EsiUrl' => 'ConVarnish\View\Helper\EsiUrlFactory'
        ],
        'aliases' => [
            'esiUrl' => 'ConVarnish\View\Helper\EsiUrl'
        ]
    ],
    'controllers' => [
        'factories' => [
            'ConVarnish\Controller\Esi' => 'ConVarnish\Controller\EsiControllerFactory'
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
