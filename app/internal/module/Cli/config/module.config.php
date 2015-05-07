<?php

return [
    'console' => [
        'router' => [
            'routes' => [
                'process-queue' => [
                    'options' => [
                        'route' => 'process-queue [<type>]',
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
                'batch-licence-status' => [
                    'options' => [
                        'route' => 'batch-cns [--verbose|-v] [--test|-t]',
                        'defaults' => [
                            'controller' => 'BatchController',
                            'action' => 'continuationNotSought'
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
    'queue' => [
        //'isLongRunningProcess' => true,
        'runFor' => 10
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
            'BatchInspectionRequestEmail' => 'Cli\Service\Processing\BatchInspectionRequestEmailProcessingService',
            'BatchContinuationNotSought' => 'Cli\Service\Processing\ContinuationNotSought',
            'Queue' => 'Cli\Service\Queue\QueueProcessor',
        ],
        'factories' => [
            'MessageConsumerManager' => 'Cli\Service\Queue\MessageConsumerManagerFactory',
        ],
    ],
    'message_consumer_manager' => [
        'invokables' => [
            // Example service
            // 'que_typ_sleep' => 'Cli\Service\Queue\Consumer\Sleep',
        ]
    ],
    'cache' => [
        'adapter' => [
            // apc_cli is not currently enabled in environments therefore change it
            'name' => 'memory',
        ]
    ],
];
