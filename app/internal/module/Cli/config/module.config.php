<?php

return [
    'console' => [
        'router' => [
            'routes' => [
                'batch-licence-status' => [
                    'options' => [
                        'route' => 'batch-licence-status [--verbose|-v]',
                        'defaults' => [
                            'controller' => 'BatchController',
                            'action' => 'licenceStatus'
                        ],
                    ],
                ],
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'BatchController' => 'Cli\Controller\BatchController',
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'BatchLicenceStatus' => 'Cli\Service\Processing\BatchLicenceStatusProcessingService'
        ],
    ],
    'cache' => [
        'adapter' => [
            // apc_cli is not currently enabled in environments therefore change it
            'name' => 'memory',
        ]
    ],

];
