<?php
return array(
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
    )
);
