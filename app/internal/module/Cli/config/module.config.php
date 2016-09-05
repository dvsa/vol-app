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
                'create-translation-csv' => [
                    'options' => [
                        'route' => 'create-translation-csv <source> <destination>',
                        'defaults' => [
                            'controller' => 'DevUtilsController',
                            'action' => 'createTranslationCsv'
                        ],
                    ],
                ],
            ]
        ]
    ],
    'queue' => [
        // 'isLongRunningProcess' => true,
        'runFor' => 60
    ],
    'controllers' => [
        'invokables' => [
            'BatchController' => 'Cli\Controller\BatchController',
            'QueueController' => 'Cli\Controller\QueueController',
            'DevUtilsController' => 'Cli\Controller\DevUtilsController',
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'BatchLicenceStatus' => 'Cli\Service\Processing\BatchLicenceStatusProcessingService',
            'BatchInboxDocuments' => 'Cli\Service\Processing\BatchInboxDocumentsProcessingService',
            'Queue' => 'Cli\Service\Queue\QueueProcessor',
        ],
        'factories' => [
            'MessageConsumerManager' => 'Cli\Service\Queue\MessageConsumerManagerFactory',
        ],
    ],
    'message_consumer_manager' => [
        'invokables' => [
            'que_typ_cont_checklist' => 'Cli\Service\Queue\Consumer\ContinuationChecklist',
            'que_typ_cont_check_rem_gen_let' =>
                'Cli\Service\Queue\Consumer\ContinuationChecklistReminderGenerateLetter',
        ]
    ],
    'cache' => [
        'adapter' => [
            // apc_cli is not currently enabled in environments therefore change it
            'name' => 'memory',
        ]
    ],
];
