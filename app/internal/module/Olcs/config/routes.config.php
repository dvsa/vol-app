<?php

use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController as TMProcessingNoteController;
use Olcs\Controller\Application\Processing\ApplicationProcessingNoteController;
use Olcs\Controller\Licence\BusRegistrationController as LicenceBusController;
use Olcs\Controller\Licence\Processing\LicenceProcessingNoteController;
use Olcs\Controller\Bus\Processing\BusProcessingDecisionController;
use Olcs\Controller\Bus\Processing\BusProcessingNoteController;
use Olcs\Controller\Operator\OperatorProcessingNoteController;
use Olcs\Controller\Bus\Details\BusDetailsController;
use Olcs\Controller\Bus\Service\BusServiceController;
use Olcs\Controller\SearchController;
use Zend\Mvc\Router\Http\Segment;

$feeActionRoute = [
    // child route config that is used in multiple places
    'type' => 'segment',
    'options' => [
        'route' => ':action[/:fee][/]',
        'constraints' => [
            'fee' => '([0-9]+,?)+',
        ],
    ],
    'may_terminate' => true,
    'child_routes' => [
        'transaction' => [
            'type' => 'segment',
            'options' => [
                'route' => 'transaction/:transaction[/]',
                'constraints' => [
                    'transaction' => '([0-9]+,?)+',
                ],
                'defaults' => [
                    'action' => 'transaction',
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'reverse' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'reverse[/]',
                        'defaults' => [
                            'action' => 'reverseTransaction',
                        ]
                    ],
                    'may_terminate' => true,
                ],
            ],
        ],
    ],
];
$feeTypeAjaxRoute = [
    // child route config that is used in multiple places
    'type' => 'segment',
    'options' => [
        'route' => 'ajax/',
    ],
    'may_terminate' => false,
    'child_routes' => [
        'single' => [
            'type' => 'segment',
            'options' => [
                'route' => 'fee-type/:id[/]',
                'constraints' => [
                    'id' => '([0-9]+,?)+',
                ],
                'defaults' => [
                    'action' => 'feeType',
                ]
            ],
            'may_terminate' => true,
        ],
        'list' => [
            'type' => 'segment',
            'options' => [
                'route' => 'fee-type-list/:date[/]',
                // 'constraints' => [
                //     'date' => '([0-9]{4}\-[0-9]{2}\-[0-9]{2})',
                // ],
                'defaults' => [
                    'action' => 'feeTypeList',
                ]
            ],
            'may_terminate' => true,
        ]
    ],
];

$feePrintReceiptRoute = [
    'type' => 'segment',
    'options' => [
        'route' => 'print-receipt/:reference[/]',
        'constraints' => [
            'reference' => '[0-9A-Za-z]+-[0-9A-F\-]+',
        ],
        'defaults' => [
            'action' => 'print',
        ],
    ],
];

$routes = [
    'dashboard' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/',
            'defaults' => [
                'controller' => Olcs\Controller\IndexController::class,
                'action' => 'index',
            ]
        ]
    ],
    'search' => [
        'type' => 'segment',
        'options' => [
            'route' => '/search[/:index[/:action][/:child_id]][/]',
            'defaults' => [
                'controller' => SearchController::class,
                'action' => 'post',
                'index' => 'licence'
            ]
        ]
    ],
    'task_action' => [
        'type' => 'segment',
        'options' => [
            'route' => '/task[/:action][/:task][/type/:type/:typeId][/]',
            'constraints' => [
                'task' => '[0-9-]+',
                'type' => '[a-z]+',
                'typeId' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'TaskController',
            ]
        ],
        'may_terminate' => true,
    ],
    'entity_lists' => [
        'type' => 'segment',
        'options' => [
            'route' => '/list/[:type]/[:value]',
            'defaults' => [
                'controller' => Olcs\Controller\IndexController::class,
                'action' => 'entityList'
            ]
        ]
    ],
    'template_lists' => [
        'type' => 'segment',
        'options' => [
            'route' => '/list-template-bookmarks/:id[/]',
            'constraints' => [
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'type' => 'licence',
                'controller' => 'DocumentGenerationController',
                'action' => 'listTemplateBookmarks'
            ]
        ]
    ],
    'fetch_tmp_document' => [
        'type' => 'segment',
        'options' => [
            'route' => '/documents/tmp/:id/:filename[/]',
            'defaults' => [
                'controller' => 'DocumentGenerationController',
                'action' => 'downloadTmp'
            ]
        ]
    ],
    // These routes are for the licence page
    'licence-no' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence-no/:licNo[/]',
            'constraints' => [
                'licNo' => '[a-zA-Z0-9]+'
            ],
            'defaults' => [
                'controller' => 'LicenceController',
                'action' => 'licNo',
            ]
        ],
    ],
    'licence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence[/]',
            'constraints' => [
                'licence' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'LicenceController',
                'action' => 'index-jump',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'active-licence-check' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'active-licence-check/:decision[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'activeLicenceCheck',
                    ]
                ],
            ],
            'curtail-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'curtail[/:status][/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'curtail'
                    ]
                ],
            ],
            'revoke-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'revoke[/:status][/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'revoke',
                    ]
                ],
            ],
            'suspend-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'suspend[/:status][/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'suspend',
                    ]
                ],
            ],
            'surrender-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'surrender[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'surrender',
                    ]
                ],
            ],
            'terminate-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'terminate[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'terminate',
                    ]
                ],
            ],
            'reset-to-valid' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'reset-to-valid[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'resetToValid',
                    ]
                ],
            ],
            'undo-surrender' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'undo-surrender[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'resetToValid',
                        'title' => 'licence-status.undo-surrender.title',
                    ]
                ],
            ],
            'undo-terminate' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'undo-terminate[/]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'resetToValid',
                        'title' => 'licence-status.undo-terminate.title',
                    ]
                ],
            ],
            'grace-periods' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'grace-periods[/:action][/:child_id][/]',
                    'defaults' => [
                        'controller' => 'LicenceGracePeriodsController',
                        'action' => 'index',
                        'child_id' => null
                    ]
                ]
            ],
            'bus' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus[/]',
                    'defaults' => [
                        'controller' => LicenceBusController::class,
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'registration' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':action[/:id][/]',
                            'constraints' => [
                                'action' => '(add|edit)',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'controller' => \Olcs\Controller\Bus\Registration\BusRegistrationController::class,
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'create_variation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'variation/create/:busRegId[/]',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => \Olcs\Controller\Bus\Registration\BusRegistrationController::class,
                                'action' => 'createVariation',
                            ]
                        ]
                    ],
                    'create_cancellation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'cancellation/create/:busRegId[/]',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => \Olcs\Controller\Bus\Registration\BusRegistrationController::class,
                                'action' => 'createCancellation',
                            ]
                        ]
                    ],
                    'request_map' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'request-map/:busRegId[/]',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => 'BusRequestMapController',
                                'action' => 'add',
                            ]
                        ]
                    ],
                    'print' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => 'print/:busRegId[/]',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => \Olcs\Controller\Bus\Registration\BusRegistrationController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'reg-letter' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => 'reg-letter[/]',
                                    'defaults' => [
                                        'action' => 'printLetter',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'bus-details' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/details[/]',
                    'defaults' => [
                        'controller' => BusDetailsController::class,
                        'action' => 'service',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'service' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'service[/]',
                            'defaults' => [
                                'action' => 'service',
                            ]
                        ],
                    ],
                    'stop' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'stop[/]',
                            'defaults' => [
                                'action' => 'stop',
                            ]
                        ],
                    ],
                    'ta' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'ta[/]',
                            'defaults' => [
                                'action' => 'ta',
                            ]
                        ],
                    ],
                    'quality' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'quality[/]',
                            'defaults' => [
                                'action' => 'quality',
                            ]
                        ],
                    ]
                ]
            ],
            'bus-short' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/short[/]',
                    'defaults' => [
                        'controller' => 'BusShortController',
                        'action' => 'edit',
                    ]
                ],
                'may_terminate' => true
            ],
            'bus-register-service' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/register-service[/]',
                    'defaults' => [
                        'controller' => BusServiceController::class,
                        'action' => 'edit',
                    ]
                ],
                'may_terminate' => true
            ],
            'bus-docs' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/docs[/]',
                    'defaults' => [
                        'controller' => 'BusDocsController',
                        'action' => 'documents',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'generate[/:doc][/]',
                            'defaults' => [
                                'type' => 'busReg',
                                'controller' => 'DocumentGenerationController',
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'finalise/:doc[/:action][/]',
                            'defaults' => [
                                'type' => 'busReg',
                                'controller' => 'DocumentFinaliseController',
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'upload[/]',
                            'defaults' => [
                                'type' => 'busReg',
                                'controller' => 'DocumentUploadController',
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'busReg',
                                'controller' => 'BusDocsController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'relink/:doc[/]',
                            'defaults' => [
                                'type' => 'busReg',
                                'controller' => 'DocumentRelinkController',
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'bus-processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/processing[/]',
                    'defaults' => [
                        'controller' => BusProcessingDecisionController::class,
                        'action' => 'details',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'decisions' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'decisions[/:action][/]',
                            'constraints' => [
                                'action' => '(cancel|grant|refuse-by-short-notice|refuse|republish|reset|withdraw)'
                            ],
                            'defaults' => [
                                'controller' => BusProcessingDecisionController::class,
                                'action' => 'details'
                            ]
                        ],
                    ],
                    'registration-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'registration-history[/:action][/]',
                            'constraints' => [
                                'action' => '(index|delete)'
                            ],
                            'defaults' => [
                                'controller' => 'BusProcessingRegistrationHistoryController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'notes[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'index|details|add|edit|delete',
                            ],
                            'defaults' => [
                                'controller' => BusProcessingNoteController::class,
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'tasks[/]',
                            'defaults' => [
                                'controller' => 'BusProcessingTaskController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'event-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'event-history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'BusRegHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => 'BusRegReadHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/fees[/]',
                    'defaults' => [
                        'controller' => 'BusFeesController',
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => $feeActionRoute,
                    'fee_type_ajax' => $feeTypeAjaxRoute,
                    'print-receipt' => $feePrintReceiptRoute,
                ]
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'cases[/]',
                    'defaults' => [
                        'controller' => 'LicenceController',
                        'action' => 'cases',
                        'page' => 1,
                        'limit' => 10,
                        'sort' => 'createdOn',
                        'order' => 'DESC'
                    ]
                ],
                'may_terminate' => true,
            ],
            'opposition' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'opposition[/]',
                    'defaults' => [
                        'action' => 'opposition',
                    ]
                ],
                'may_terminate' => true,
            ],
            'documents' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'documents[/]',
                    'defaults' => [
                        'controller' => 'LicenceDocsController',
                        'action' => 'documents',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'add-sla' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'add-sla/:entityType/:entityId',
                            'constraints' => [
                                'entityId' => '[0-9]+',
                                'entityType' => '(document)',
                            ],
                            'defaults' => [
                                'controller' => 'LicenceDocumentSlaTargetDateController',
                                'action' => 'addSla'
                            ]
                        ],
                    ],
                    'edit-sla' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'edit-sla/:entityType/:entityId',
                            'constraints' => [
                                'entityId' => '[0-9]+',
                                'entityType' => '(document)',
                            ],
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'LicenceDocumentSlaTargetDateController',
                                'action' => 'editSla'
                            ]
                        ],
                    ],
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'generate[/:doc][/]',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'DocumentGenerationController',
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'finalise/:doc[/:action][/]',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'DocumentFinaliseController',
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'upload[/]',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'DocumentUploadController',
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'LicenceDocsController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'relink/:doc[/]',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'DocumentRelinkController',
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'processing[/]',
                    'defaults' => [
                        'controller' => 'LicenceProcessingOverviewController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'publications' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'publications[/:action][/:id][/]',
                            'defaults' => [
                                'controller' => 'LicenceProcessingPublicationsController',
                                'action' => 'index'
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                                'action' => '(index|edit|delete)'
                            ]
                        ],
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'tasks[/]',
                            'defaults' => [
                                'controller' => 'LicenceProcessingTasksController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'notes' => [ // Licence Notes
                        'type' => 'segment',
                        'options' => [
                            'route' => 'notes[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'index|details|add|edit|delete',
                            ],
                            'defaults' => [
                                'controller' => LicenceProcessingNoteController::class,
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'inspection-request' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'inspection-request[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'LicenceProcessingInspectionRequestController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'event-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'event-history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'LicenceHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => 'LicenceReadHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'fees[/]',
                    'defaults' => [
                        'controller' => 'LicenceFeesController',
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => $feeActionRoute,
                    'fee_type_ajax' => $feeTypeAjaxRoute,
                    'print-receipt' => $feePrintReceiptRoute,
                ]
            ],
            'update-continuation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'update-continuation[/]',
                    'defaults' => [
                        'controller' => \Olcs\Controller\Licence\ContinuationController::class,
                        'action' => 'update-continuation',
                        'title' => 'licence-status.undo-terminate.title @todo',
                    ]
                ],
            ],
        ]
    ],
    'operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/:organisation[/]',
            'constraints' => [
                'organisation' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => Olcs\Controller\Operator\OperatorController::class,
                'action' => 'index-jump',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'business-details' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'business-details[/]',
                    'defaults' => [
                        'controller' => 'OperatorBusinessDetailsController',
                        'action' => 'index',
                    ]
                ]
            ],
            'people' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'people[/:action][/:id][/]',
                    'constraints' => [
                        'action' => 'add|edit|delete',
                        'id' => '([0-9]+,?)+',
                    ],
                    'defaults' => [
                        'controller' => 'OperatorPeopleController',
                        'action' => 'index',
                    ]
                ]
            ],
            'licences' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'licences[/]',
                    'defaults' => [
                        'controller' => 'OperatorLicencesApplicationsController',
                        'action' => 'licences',
                    ]
                ]
            ],
            'applications' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'applications[/]',
                    'defaults' => [
                        'controller' => 'OperatorLicencesApplicationsController',
                        'action' => 'applications',
                    ]
                ]
            ],
            'new-application' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'new-application[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorController::class,
                        'action' => 'newApplication',
                    ]
                ]
            ],
            'irfo' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'irfo[/]',
                    'defaults' => array(
                        'controller' => 'OperatorIrfoDetailsController',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => [
                    'details' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => 'details[/]',
                            'defaults' => [
                                'controller' => 'OperatorIrfoDetailsController',
                                'action' => 'edit'
                            ]
                        ],
                    ],
                    'gv-permits' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => 'gv-permits[/:action][/:id][/]',
                            'constraints' => [
                                'action' => '(add|details|reset|approve|generate|withdraw|refuse)',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'controller' => 'OperatorIrfoGvPermitsController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'psv-authorisations' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => 'psv-authorisations[/:action][/:id][/]',
                            'constraints' => [
                                'action' => '(add|edit|reset)',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'controller' => 'OperatorIrfoPsvAuthorisationsController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                ],
            ),
            'processing' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'processing[/]',
                    'defaults' => array(
                        'controller' => 'OperatorHistoryController',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => [
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'OperatorHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => 'OperatorReadHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'notes' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => 'notes[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'index|details|add|edit|delete',
                            ],
                            'defaults' => [
                                'controller' => OperatorProcessingNoteController::class,
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'tasks[/]',
                            'defaults' => [
                                'controller' => 'OperatorProcessingTasksController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                ],
            ),
            'fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'fees[/]',
                    'defaults' => [
                        'controller' => 'OperatorFeesController',
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => $feeActionRoute,
                    'fee_type_ajax' => $feeTypeAjaxRoute,
                    'print-receipt' => $feePrintReceiptRoute,
                ]
            ],
            'documents' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'documents[/]',
                    'defaults' => [
                        'controller' => 'OperatorDocsController',
                        'action' => 'documents',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'generate[/:doc][/]',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'DocumentGenerationController',
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'finalise/:doc[/:action][/]',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'DocumentFinaliseController',
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'upload[/]',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'DocumentUploadController',
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'OperatorDocsController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'relink/:doc[/]',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'DocumentRelinkController',
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'disqualify' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'disqualify[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\DisqualifyController::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'disqualify_person' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'person/:person/disqualify[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\DisqualifyController::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'merge' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'merge[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorController::class,
                        'action' => 'merge',
                    ]
                ],
            ],
        ]
    ],
    'operator-lookup' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/lookup[/]',
            'defaults' => [
                'controller' => Olcs\Controller\Operator\OperatorController::class,
                'action' => 'lookup',
            ]
        ],
    ],
    'create_operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/create[/]',
            'defaults' => [
                'controller' => 'OperatorBusinessDetailsController',
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
    ],
    'operator-unlicensed' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator-unlicensed/:organisation[/]',
            'constraints' => [
                'organisation' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => Olcs\Controller\Operator\UnlicensedBusinessDetailsController::class,
                'action' => 'index-jump',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'business-details' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'business-details[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\UnlicensedBusinessDetailsController::class,
                        'action' => 'index',
                    ]
                ]
            ],
            'vehicles' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'vehicles[/:action[/:id]][/]',
                    'constraints' => [
                        'action' => 'index|details|add|edit|delete',
                    ],
                    'defaults' => [
                        'controller' => 'UnlicensedOperatorVehiclesController',
                        'action' => 'index',
                    ]
                ]
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'cases[/]',
                    'defaults' => [
                        'controller' => 'UnlicensedCasesOperatorController',
                        'action' => 'cases',
                    ]
                ]
            ],
        ]
    ],
    'create_unlicensed_operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator-unlicensed/create[/]',
            'defaults' => [
                'controller' => Olcs\Controller\Operator\UnlicensedBusinessDetailsController::class,
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
    ],
    'create_variation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/variation/create/:licence[/]',
            'defaults' => [
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
                'controller' => 'LvaLicence',
                'action' => 'createVariation'
            ]
        ]
    ],
    'print_licence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/print/:licence[/]',
            'defaults' => [
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
                'controller' => 'LvaLicence',
                'action' => 'print'
            ]
        ]
    ],
    // Transport Manager routes
    'transport-manager' => [
        'type' => 'segment',
        'options' => [
            'route' => '/transport-manager/:transportManager[/]',
            'constraints' => [
                'transportManager' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'TMController',
                'action' => 'index-jump',
                'transportManager' => ''
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'details' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'details[/]',
                    'defaults' => [
                        'controller' => 'TMDetailsDetailController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'competences' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'competences[/:action][/:id][/]',
                            'defaults' => [
                                'controller' => 'TMDetailsCompetenceController',
                                'action' => 'index',
                            ],
                            'constraints' => [
                                'id' => '(\d+)(,\d+)*'
                            ],
                        ],
                    ],
                    'responsibilities' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'responsibilities[/:action[/:id][/title/:title]][/]',
                            'defaults' => [
                                'controller' => 'TMDetailsResponsibilityController',
                                'action' => 'index',
                                'title' => 0
                            ]
                        ]
                    ],
                    'employment' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'employment[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'TMDetailsEmploymentController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'previous-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'previous-history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'TMDetailsPreviousHistoryController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                ],
            ],
            'processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'processing[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'index-processing-jump',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'decisions' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'decisions[/]',
                            'defaults' => [
                                'controller' => 'TMProcessingDecisionController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'TMProcessingHistoryController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'publication' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'publication[/:action][/:id][/]',
                            'defaults' => [
                                'controller' => 'TMProcessingPublicationController',
                                'action' => 'index',
                            ],
                            'constraints' => [
                                'id' => '[0-9]+',
                                'action' => '(index|edit|delete)'
                            ]
                        ]
                    ],
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'notes[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'index|details|add|edit|delete',
                            ],
                            'defaults' => [
                                'controller' => TMProcessingNoteController::class,
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'tasks[/]',
                            'defaults' => [
                                'controller' => 'TMProcessingTaskController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'event-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'event-history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => 'TransportManagerHistoryController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => 'TransportManagerReadHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ],
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'cases[/:action][/:id][/]',
                    'defaults' => [
                        'controller' => 'TMCaseController',
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                        'action' => '(add|edit|delete|index)'
                    ],
                ]
            ],
            'documents' => [
                'type' => 'segment',
                'may_terminate' => true,
                'options' => [
                    'route' => 'documents[/]',
                    'defaults' => [
                        'controller' => 'TMDocumentController',
                        'action' => 'index',
                    ]
                ],
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'generate[/:doc][/]',
                            'defaults' => [
                                'type' => 'transportManager',
                                'controller' => 'DocumentGenerationController',
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'finalise/:doc[/:action][/]',
                            'defaults' => [
                                'type' => 'transportManager',
                                'controller' => 'DocumentFinaliseController',
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'upload[/]',
                            'defaults' => [
                                'type' => 'transportManager',
                                'controller' => 'DocumentUploadController',
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'transportManager',
                                'controller' => 'TMDocumentController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'relink/:doc[/]',
                            'defaults' => [
                                'type' => 'transportManager',
                                'controller' => 'DocumentRelinkController',
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'can-remove' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'can-remove[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'canRemove'
                    ],
                ],
            ],
            'remove' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'remove[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'remove'
                    ],
                ],
            ],
            'merge' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'merge[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'merge'
                    ],
                ],
            ],
            'unmerge' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'unmerge[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'unmerge'
                    ],
                ],
            ],
            'undo-disqualification' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'undo-disqualification[/]',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'undoDisqualification'
                    ],
                ],
            ],
        ],
    ],
    'transport-manager-lookup' => [
        'type' => 'segment',
        'options' => [
            'route' => '/transport-manager/lookup[/]',
            'defaults' => [
                'controller' => 'TMController',
                'action' => 'lookup',
            ],
        ],
        'may_terminate' => true,
    ],
     'historic-tm' => [
        'type' => 'segment',
        'options' => [
            'route' => '/historic-tm/:historicId[/]',
            'constraints' => [
                'action' => 'details',
                'historicId' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'HistoricTmController',
                'action' => 'details',
            ],
        ],
        'may_terminate' => true,
    ],
    'create_transport_manager' => [
        'type' => 'segment',
        'options' => [
            'route' => '/transport-manager/create[/]',
            'defaults' => [
                'controller' => 'TMDetailsDetailController',
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
    ],
    'split-screen' => [
        'type' => 'segment',
        'options' => [
            'route' => '/split[/]',
            'defaults' => [
                'controller' => 'SplitScreenController',
                'action' => 'index'
            ]
        ]
    ],
    'disqualify-person' => [
        'type' => 'segment',
        'options' => [
            'route' => '/disqualify-person/:person',
            'defaults' => [
                'controller' => Olcs\Controller\DisqualifyController::class,
                'action' => 'index',
            ],
        ],
        'may_terminate' => false,
        'child_routes' => [
            'application' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/application/:application[/]',
                ],
            ],
            'variation' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/variation/:variation[/]',
                ],
            ],
            'licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/licence/:licence[/]',
                ],
            ],
        ],
    ],
];

$sectionConfig = new \Common\Service\Data\SectionConfig();

$routes = array_merge($routes, $sectionConfig->getAllRoutes());

$routes['lva-licence']['child_routes'] = array_merge(
    $routes['lva-licence']['child_routes'],
    array(
        'overview' => array(
            'type' => 'segment',
            'options' => array(
                'route' => '',
                'defaults' => array(
                    'controller' => 'LvaLicence',
                    'action' => 'index'
                )
            )
        ),
        'variation' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'variation[/:redirectRoute][/]',
                'defaults' => array(
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                )
            )
        )
    )
);

$routes['lva-variation']['child_routes'] = array_merge(
    $routes['lva-variation']['child_routes'],
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Review',
                    'action' => 'index'
                )
            )
        ),
        'interim' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'interim[/:action][/]',
                'defaults' => array(
                    'controller' => 'InterimVariationController',
                    'action' => 'index'
                )
            )
        ),
        'grant' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'grant[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Grant',
                    'action' => 'grant'
                )
            )
        ),
        'withdraw' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'withdraw[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Withdraw',
                    'action' => 'index'
                )
            )
        ),
        'refuse' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'refuse[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Refuse',
                    'action' => 'index'
                )
            )
        ),
        'revive-application' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'revive-application[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Revive',
                    'action' => 'index'
                )
            )
        ),
        'approve-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'approve-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'approveSchedule41'
                )
            )
        ),
        'reset-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'reset-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'resetSchedule41'
                )
            )
        ),
        'refuse-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'refuse-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'refuseSchedule41'
                )
            )
        ),
        'schedule41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'schedule41[/]',
                'defaults' => array(
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'licenceSearch'
                )
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'transfer' => array(
                    'type' => 'segment',
                    'options' => array(
                        'route' => 'transfer[/:licNo][/]',
                        'defaults' => array(
                            'controller' => 'VariationSchedule41Controller',
                            'action' => 'transfer'
                        )
                    )
                )
            )
        ),
        'publish' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'publish[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Publish',
                    'action' => 'index'
                )
            )
        ),
        'submit' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submit[/]',
                'defaults' => array(
                    'controller' => 'LvaVariation/Submit',
                    'action' => 'index'
                )
            )
        ),
        'overview' => [
            'type' => Segment::class,
            'options' => [
                'route' => '',
                'defaults' => [
                    'controller' => 'LvaVariation',
                    'action' => 'index',
                ],
            ],
        ],
    )
);

$routes['lva-application']['child_routes'] = array_merge(
    $routes['lva-application']['child_routes'],
    array(
        'review' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'review[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Review',
                    'action' => 'index'
                )
            )
        ),
        'grant' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'grant[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Grant',
                    'action' => 'grant'
                )
            )
        ),
        'undo-grant' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'undo-grant[/]',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'undoGrant'
                )
            )
        ),
        'not-taken-up' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'not-taken-up[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/NotTakenUp',
                    'action' => 'index'
                )
            )
        ),
        'revive-application' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'revive-application[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/ReviveApplication',
                    'action' => 'index'
                )
            )
        ),
        'withdraw' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'withdraw[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Withdraw',
                    'action' => 'index'
                )
            )
        ),
        'refuse' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'refuse[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Refuse',
                    'action' => 'index'
                )
            )
        ),
        'submit' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'submit[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Submit',
                    'action' => 'index'
                )
            )
        ),
        'schedule41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'schedule41[/]',
                'defaults' => array(
                    'controller' => 'ApplicationSchedule41Controller',
                    'action' => 'licenceSearch'
                )
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'transfer' => array(
                    'type' => 'segment',
                    'options' => array(
                        'route' => 'transfer[/:licNo][/]',
                        'defaults' => array(
                            'controller' => 'ApplicationSchedule41Controller',
                            'action' => 'transfer'
                        )
                    )
                )
            )
        ),
        'approve-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'approve-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'ApplicationSchedule41Controller',
                    'action' => 'approveSchedule41'
                )
            )
        ),
        'reset-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'reset-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'ApplicationSchedule41Controller',
                    'action' => 'resetSchedule41'
                )
            )
        ),
        'refuse-schedule-41' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'refuse-schedule-41[/]',
                'defaults' => array(
                    'controller' => 'ApplicationSchedule41Controller',
                    'action' => 'refuseSchedule41'
                )
            )
        ),
        'overview' => array(
            'type' => 'segment',
            'options' => array(
                'route' => '',
                'defaults' => array(
                    'controller' => 'LvaApplication',
                    'action' => 'index'
                )
            )
        ),
        'change-of-entity' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'change-of-entity[/:changeId][/]',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'changeOfEntity'
                )
            )
        ),
        'case' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'case[/]',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'case'
                )
            )
        ),
        'opposition' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'opposition[/]',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'opposition'
                )
            )
        ),
        'documents' => [
            'type' => 'segment',
            'options' => [
                'route' => 'documents[/]',
                'defaults' => [
                    'controller' => 'ApplicationDocsController',
                    'action' => 'documents',
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'generate' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'generate[/:doc][/]',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'DocumentGenerationController',
                            'action' => 'generate'
                        ]
                    ],
                ],
                'finalise' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'finalise/:doc[/:action][/]',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'DocumentFinaliseController',
                            'action' => 'finalise'
                        ]
                    ],
                ],
                'upload' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'upload[/]',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'DocumentUploadController',
                            'action' => 'upload'
                        ]
                    ],
                ],
                'delete' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'delete/:doc[/]',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'ApplicationDocsController',
                            'action' => 'delete-document'
                        ]
                    ],
                ],
                'relink' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'relink/:doc[/]',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'DocumentRelinkController',
                            'action' => 'relink'
                        ]
                    ],
                ],
            ],
        ],
        'processing' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'processing[/]',
                'defaults' => array(
                    'controller' => 'ApplicationProcessingOverviewController',
                    'action' => 'index'
                )
            ),
            'may_terminate' => true,
            'child_routes' => [
                'publications' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'publications[/:action][/:id][/]',
                        'defaults' => [
                            'controller' => 'ApplicationProcessingPublicationsController',
                            'action' => 'index'
                        ]
                    ],
                ],
                'tasks' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => 'tasks[/]',
                        'defaults' => [
                            'controller' => 'ApplicationProcessingTasksController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'inspection-request' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'inspection-request[/:action[/:id]][/]',
                        'defaults' => [
                            'controller' => 'ApplicationProcessingInspectionRequestController',
                            'action' => 'index'
                        ]
                    ],
                ],
                'notes' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => 'notes[/:action[/:id]][/]',
                        'defaults' => [
                            'controller' => ApplicationProcessingNoteController::class,
                            'action' => 'index'
                        ]
                    ],
                ],
                'event-history' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => 'event-history[/:action[/:id]][/]',
                        'defaults' => [
                            'controller' => 'ApplicationHistoryController',
                            'action' => 'index'
                        ]
                    ],
                ],
                'read-history' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => 'read-history[/]',
                        'defaults' => [
                            'controller' => 'ApplicationReadHistoryController',
                            'action' => 'index'
                        ]
                    ],
                ],
            ],
        ),
        'fees' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'fees[/]',
                'defaults' => array(
                    'controller' => 'ApplicationFeesController',
                    'action' => 'fees',
                )
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'fee_action' => $feeActionRoute,
                'fee_type_ajax' => $feeTypeAjaxRoute,
                'print-receipt' => $feePrintReceiptRoute,
            )
        ),
        'interim' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'interim[/:action][/]',
                'defaults' => array(
                    'controller' => 'InterimApplicationController',
                    'action' => 'index'
                )
            )
        ),
        'publish' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'publish[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Publish',
                    'action' => 'index'
                )
            )
        ),
    )
);

/**
 * To find it easier to locate resources/controllers,
 * it would be convenient to start moving each section
 * of the application into individual route configs.
 * Move the appropriate section of the array into a
 * seperate and meaningful directory.  After doing so
 * add the file to the $routeConfigs[] below.  This will
 * be merged using ArrayUtils.
 * Do not forget to test!!
 */
$routeConfigs = [
    __DIR__  . '/routes/licence/case.php',
];

// Merge all module config options
foreach($routeConfigs as $routeConfig) {
    $routes = \Zend\Stdlib\ArrayUtils::merge($routes, include $routeConfig);
}

return $routes;
