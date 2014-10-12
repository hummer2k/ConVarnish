<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ConVarnish\Controller\Esi' => 'ConVarnish\Controller\EsiController'
        )
    ),
    'router' => array(
        'routes' => array(
            'esi' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/esi/:block',
                    'constraints' => array(
                        'block' => '[A-Za-z0-9.-_]+'
                    ),
                    'defaults' => array(
                        'controller' => 'ConVarnish\Controller\Esi',
                        'action' => 'block'
                    )
                )
            )
        )
    ),
    'varnish' => array(
        'cacheable_routes' => array(
            'home' => array(
                'ttl' => 60
            )
        )
    )
);
