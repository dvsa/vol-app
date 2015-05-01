<?php

return [
    'console' => [
        'router' => [
            'routes' => [
                'process-queue' => [
                    'options' => [
                        'route' => 'process-queue',
                        'defaults' => [
                            'controller' => 'QueueController',
                            'action' => 'index'
                        ],
                    ],
                ],
                'batch-licence-status' => [
                    'options' => [
                        'route' => 'batch-licence-status [--verbose|-v]',
                        'defaults' => [
                            'controller' => 'BatchController',
                            'action' => 'licenceStatus'
                        ],
                    ],
                ],
                'inspection-request-email' => [
                    'options' => [
                        'route' => 'inspection-request-email [--verbose|-v]',
                        'defaults' => [
                            'controller' => 'BatchController',
                            'action' => 'inspectionRequestEmail'
                        ],
                    ],
                ],
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'BatchController' => 'Cli\Controller\BatchController',
            'QueueController' => 'Cli\Controller\QueueController',
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'BatchLicenceStatus' => 'Cli\Service\Processing\BatchLicenceStatusProcessingService',
            'Queue' => 'Cli\Service\Processing\QueueProcessingService',
            'BatchInspectionRequestEmail' => 'Cli\Service\Processing\BatchInspectionRequestEmailProcessingService',
        ],
    ],
    'cache' => [
        'adapter' => [
            // apc_cli is not currently enabled in environments therefore change it
            'name' => 'memory',
        ]
    ],
];
