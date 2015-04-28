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
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'BatchLicenceStatus' => 'Cli\Service\Processing\BatchLicenceStatusProcessingService',
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
