<?php

use Olcs\Controller\Application\Processing\ApplicationProcessingInspectionRequestController;
use Olcs\Controller\Application\Processing\ApplicationProcessingNoteController;
use Olcs\Controller\Application\Processing\ApplicationProcessingPublicationsController;
use Olcs\Controller\IrhpPermits\IrhpApplicationFeesController;
use Olcs\Controller\Licence\SurrenderController;
use Olcs\Controller\Operator\OperatorBusinessDetailsController;
use Olcs\Controller\TaskController;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailController;
use Olcs\Controller\Operator\OperatorFeesController;
use Olcs\Controller\Operator\OperatorProcessingTasksController;
use Olcs\Controller\TransportManager\Processing\HistoryController;
use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController as TMProcessingNoteController;
use Olcs\Controller\Licence\BusRegistrationController as LicenceBusController;
use Olcs\Controller\Licence\Processing\LicenceProcessingNoteController;
use Olcs\Controller\Bus\Processing\BusProcessingDecisionController;
use Olcs\Controller\Bus\Processing\BusProcessingNoteController;
use Olcs\Controller\Operator\OperatorProcessingNoteController;
use Olcs\Controller\IrhpPermits\ApplicationController as IrhpPermitsApplicationController;
use Olcs\Controller\IrhpPermits\PermitController as IrhpPermitsPermitController;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingOverviewController;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingNoteController;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingTasksController;
use Olcs\Controller\IrhpPermits\IrhpApplicationProcessingReadHistoryController;
use Olcs\Controller\Bus\Details\BusDetailsController;
use Olcs\Controller\Bus\Service\BusServiceController;
use Olcs\Controller\SearchController;
use Laminas\Router\Http\Segment;
use Olcs\Controller\TransportManager as TmCntr;
use Olcs\Controller\Operator as OperatorControllers;
use Olcs\Controller\Application as ApplicationControllers;
use Olcs\Controller\Licence as LicenceControllers;
use Olcs\Controller\Document as DocumentControllers;

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
                'controller' => TaskController::class,
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
                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
                'action' => 'listTemplateBookmarks'
            ]
        ]
    ],
    'fetch_tmp_document' => [
        'type' => 'segment',
        'options' => [
            'route' => '/documents/tmp/:id/:filename[/]',
            'defaults' => [
                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
            'surrender-details' => [
                'may_terminate' => false,
                'type' => 'segment',
                'options' => [
                    'route' => 'surrender-details[/]',
                ],
                'child_routes' => [
                    'GET' => [
                        'may_terminate' => true,
                        'type' => \Laminas\Router\Http\Method::class,
                        'options' => [
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => SurrenderController::class,
                                'action' => 'index'
                            ],
                        ],
                    ],
                    'POST' => [
                        'may_terminate' => true,
                        'type' => \Laminas\Router\Http\Method::class,
                        'options' => [
                            'verb' => 'POST',
                            'defaults' => [
                                'controller' => SurrenderController::class,
                                'action' => 'surrender'
                            ],
                        ],
                    ],
                    'withdraw' => [
                        'may_terminate' => false,
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => 'withdraw[/]',
                        ],
                        'child_routes' => [
                            'GET' => [
                                'may_terminate' => true,
                                'type' => \Laminas\Router\Http\Method::class,
                                'options' => [
                                    'verb' => 'GET',
                                    'defaults' => [
                                        'controller' => SurrenderController::class,
                                        'action' => 'withdraw'
                                    ],
                                ],
                            ],
                            'POST' => [
                                'may_terminate' => true,
                                'type' => \Laminas\Router\Http\Method::class,
                                'options' => [
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => SurrenderController::class,
                                        'action' => 'confirmWithdraw'
                                    ],
                                ],
                            ]
                        ]
                    ],
                    'surrender-checks' => [
                        'may_terminate' => true,
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => 'surrender-checks',
                            'defaults' => [
                                'controller' => SurrenderController::class,
                                'action' => 'surrenderChecks'
                            ],
                        ],
                    ],
                ]
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
                        'action' => 'undoSurrender',
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
                                'controller' => Olcs\Controller\Bus\BusRequestMapController::class,
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
                        'controller' => Olcs\Controller\Bus\Short\BusShortController::class,
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
            'bus_conversation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/conversation[/]',
                    'verb' => 'GET',
                    'defaults' => [
                        'controller' => Olcs\Controller\Messages\BusConversationListController::class,
                        'action' => 'index',
                        'type' => 'busReg',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'view' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\BusConversationMessagesController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'new' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'new[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\BusCreateConversationController::class,
                                'action' => 'add'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'close' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation/close[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\BusCloseConversationController::class,
                                'action' => 'confirm'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'disable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'disable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\BusEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'disable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\BusEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'disable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'enable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'enable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\BusEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'enable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\BusEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'enable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'bus-docs' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'bus/:busRegId/docs[/]',
                    'defaults' => [
                        'controller' => \Olcs\Controller\Bus\Docs\BusDocsController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
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
                                'controller' => DocumentControllers\DocumentUploadController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
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
                                'controller' => Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController::class,
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
                                'controller' => Olcs\Controller\Bus\Processing\HistoryController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Bus\Processing\ReadHistoryController::class,
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
            'conversation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'conversation[/]',
                    'verb' => 'GET',
                    'defaults' => [
                        'controller' => Olcs\Controller\Messages\LicenceConversationListController::class,
                        'action' => 'index',
                        'type' => 'licence',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'view' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\LicenceConversationMessagesController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'fileuploads' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'fileuploads/',
                            'verb' => 'POST',
                            'defaults' => [
                                'controller' => \Olcs\Controller\Messages\EnableDisableFileUploadController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'enable' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'enable[/]',
                                    'defaults' => [
                                        'action' => 'enable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'disable' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'disable[/]',
                                    'defaults' => [
                                        'action' => 'disable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'new' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'new[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\LicenceCreateConversationController::class,
                                'action' => 'add'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'close' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation/close[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\LicenceCloseConversationController::class,
                                'action' => 'confirm'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'disable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'disable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\LicenceEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'disable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\LicenceEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'disable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'enable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'enable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\LicenceEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'enable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\LicenceEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'enable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
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
                                'controller' => Olcs\Controller\Sla\LicenceDocumentSlaTargetDateController::class,
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
                                'controller' => Olcs\Controller\Sla\LicenceDocumentSlaTargetDateController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
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
                                'controller' => DocumentControllers\DocumentUploadController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'irhp-application-fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-application/:irhpAppId/fees[/]',
                    'constraints' => [
                        'irhpAppId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => IrhpApplicationFeesController::class,
                        'action' => 'dashRedirect',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => $feeActionRoute,
                    'fee_type_ajax' => $feeTypeAjaxRoute,
                    'print-receipt' => $feePrintReceiptRoute,
                    'table' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'table[/]',
                            'defaults' => [
                                'action' => 'fees'
                            ]
                        ]
                    ],
                ]
            ],
            'irhp-permits' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-permits[/]',
                    'defaults' => [
                        'controller' => IrhpPermitsApplicationController::class,
                        'action' => 'redirect'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'application' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'application[/]',
                            'defaults' => [
                                'controller' => IrhpPermitsApplicationController::class,
                                'action' => 'index'
                            ],
                        ]
                    ],
                    'permit' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'permit[/]',
                            'defaults' => [
                                'controller' => IrhpPermitsPermitController::class,
                                'action' => 'index'
                            ],
                        ]
                    ],
                ],
            ],
            'irhp-application' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-application[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\IrhpPermits\IrhpApplicationController::class,
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'add' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'add[/]:permitTypeId[/]',
                            'defaults' => [
                                'action' => 'add'
                            ],
                            'constraints' => [
                                'permitTypeId' => '[0-9]+',
                            ],
                        ]
                    ],
                    'selectType' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'type[/]',
                            'defaults' => [
                                'action' => 'selectType'
                            ]
                        ]
                    ],
                    'availableYears' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'available-years[/]',
                            'defaults' => [
                                'action' => 'availableYears'
                            ]
                        ]
                    ],
                    'availableStocks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'available-stocks[/]',
                            'defaults' => [
                                'action' => 'availableStocks'
                            ]
                        ]
                    ],
                    'availableCountries' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'available-countries[/]',
                            'defaults' => [
                                'action' => 'availableCountries'
                            ]
                        ]
                    ],
                    'application' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':action/:irhpAppId[/][:permitId]',
                            'constraints' => [
                                'action' => 'details|edit|submit|accept|decline|cancel|terminate|withdraw|grant|preGrant|preGrantEdit|preGrantAdd|preGrantDelete|ranges|reviveFromWithdrawn|reviveFromUnsuccessful|viewpermits|resetToNotYetSubmittedFromValid|resetToNotYetSubmittedFromCancelled',
                                'irhpAppId' => '[0-9]+',
                                'permitId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'edit'
                            ]
                        ]
                    ],
                    'irhp-permits' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':irhpAppId[/]:permitTypeId[/]irhp-permits[/:action][/:irhpPermitId][/]',
                            'constraints' => [
                                'irhpAppId' => '[0-9]+',
                                'action' => 'requestReplacement|terminatePermit',
                                'irhpPermitId' => '[0-9]+',
                                'permitTypeId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => Olcs\Controller\IrhpPermits\IrhpPermitController::class,
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'irhp-application-conversation' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-application/:irhpAppId/conversation[/]',
                    'verb' => 'GET',
                    'defaults' => [
                        'controller' => Olcs\Controller\Messages\IrhpApplicationConversationListController::class,
                        'action' => 'index',
                        'type' => 'irhp-application',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'view' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\IrhpApplicationConversationMessagesController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'new' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'new[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\IrhpApplicationCreateConversationController::class,
                                'action' => 'add'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'close' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => ':conversation/close[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\IrhpApplicationCloseConversationController::class,
                                'action' => 'confirm'
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'disable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'disable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\IrhpApplicationEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'disable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\IrhpApplicationEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'disable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'enable' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'enable[/]',
                            'verb' => 'GET',
                            'defaults' => [
                                'controller' => Olcs\Controller\Messages\IrhpApplicationEnableDisableMessagingController::class,
                                'action' => 'index',
                                'type' => 'enable',
                            ]
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'popup' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'popup[/]',
                                    'verb' => 'POST',
                                    'defaults' => [
                                        'controller' => Olcs\Controller\Messages\IrhpApplicationEnableDisableMessagingController::class,
                                        'action' => 'popup',
                                        'type' => 'enable',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'irhp-application-docs' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-application/documents/:irhpAppId[/]',
                    'constraints' => [
                        'irhpAppId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => \Olcs\Controller\IrhpPermits\IrhpApplicationDocsController::class,
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
                                'type' => 'irhpApplication',
                                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'finalise/:doc[/:action][/]',
                            'defaults' => [
                                'type' => 'irhpApplication',
                                'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'upload[/]',
                            'defaults' => [
                                'type' => 'irhpApplication',
                                'controller' => DocumentControllers\DocumentUploadController::class,
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'irhpApplication',
                                'controller' => \Olcs\Controller\IrhpPermits\IrhpApplicationDocsController::class,
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'relink/:doc[/]',
                            'defaults' => [
                                'type' => 'irhpApplication',
                                'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
                                'action' => 'relink'
                            ]
                        ],
                    ],
                ],
            ],
            'irhp-application-processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irhp-application/processing/:irhpAppId[/]',
                    'constraints' => [
                        'irhpAppId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => IrhpApplicationProcessingOverviewController::class,
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'notes[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'index|details|add|edit|delete',
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => IrhpApplicationProcessingNoteController::class,
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'tasks[/]',
                            'defaults' => [
                                'controller' => IrhpApplicationProcessingTasksController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'event-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'event-history[/:action[/:id]][/]',
                            'constraints' => [
                                'action' => 'edit',
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => Olcs\Controller\IrhpPermits\IrhpApplicationProcessingHistoryController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => IrhpApplicationProcessingReadHistoryController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'processing[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController::class,
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
                                'controller' => Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController::class,
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
                                'controller' => LicenceControllers\Processing\LicenceProcessingTasksController::class,
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
                                'controller' => LicenceControllers\Processing\LicenceProcessingInspectionRequestController::class,
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'event-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'event-history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => LicenceControllers\Processing\HistoryController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Licence\Processing\ReadHistoryController::class,
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
                        'controller' => LicenceControllers\Fees\LicenceFeesController::class,
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
                        'controller' => OperatorBusinessDetailsController::class,
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
                        'controller' => Olcs\Controller\Operator\OperatorPeopleController::class,
                        'action' => 'index',
                    ]
                ]
            ],
            'licences' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'licences[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorLicencesApplicationsController::class,
                        'action' => 'licences',
                    ]
                ]
            ],
            'applications' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'applications[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorLicencesApplicationsController::class,
                        'action' => 'applications',
                    ]
                ]
            ],
            'users' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'users[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorUsersController::class,
                        'action' => 'index',
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
            'irfo' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'irfo[/]',
                    'defaults' => [
                        'controller' => Olcs\Controller\Operator\OperatorIrfoDetailsController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'details' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => 'details[/]',
                            'defaults' => [
                                'controller' => Olcs\Controller\Operator\OperatorIrfoDetailsController::class,
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
                                'controller' => Olcs\Controller\Operator\OperatorIrfoGvPermitsController::class,
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
                                'controller' => Olcs\Controller\Operator\OperatorIrfoPsvAuthorisationsController::class,
                                'action' => 'index'
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
                        'controller' => OperatorControllers\HistoryController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => OperatorControllers\HistoryController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'read-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'read-history[/]',
                            'defaults' => [
                                'controller' => OperatorControllers\Processing\ReadHistoryController::class,
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
                                'controller' => OperatorProcessingTasksController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                ],
            ],
            'fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'fees[/]',
                    'defaults' => [
                        'controller' => OperatorFeesController::class,
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
                        'controller' => OperatorControllers\Docs\OperatorDocsController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
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
                                'controller' => DocumentControllers\DocumentUploadController::class,
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
                                'controller' => OperatorControllers\Docs\OperatorDocsController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
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
                'controller' => OperatorBusinessDetailsController::class,
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
                'controller' => OperatorControllers\UnlicensedBusinessDetailsController::class,
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
                        'controller' => Olcs\Controller\Operator\UnlicensedOperatorVehiclesController::class,
                        'action' => 'index',
                    ]
                ]
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'cases[/]',
                    'defaults' => [
                        'controller' => OperatorControllers\Cases\UnlicensedCasesOperatorController::class,
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
                        'controller' => TransportManagerDetailsDetailController::class,
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
                                'controller' => Olcs\Controller\TransportManager\Details\TransportManagerDetailsCompetenceController::class,
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
                                'controller' => TmCntr\Details\TransportManagerDetailsResponsibilityController::class,
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
                                'controller' => Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentController::class,
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
                    'history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'history[/:action[/:id]][/]',
                            'defaults' => [
                                'controller' => TmCntr\Processing\HistoryController::class,
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'publication' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'publication[/:action][/:id][/]',
                            'defaults' => [
                                'controller' => TmCntr\Processing\PublicationController::class,
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
                                'controller' => Olcs\Controller\TransportManager\Processing\HistoryController::class,
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
                                'controller' => Olcs\Controller\TransportManager\Processing\ReadHistoryController::class,
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
                        'controller' => Olcs\Controller\TransportManager\TransportManagerCaseController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
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
                                'controller' => DocumentControllers\DocumentUploadController::class,
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
                                'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
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
    'create_transport_manager' => [
        'type' => 'segment',
        'options' => [
            'route' => '/transport-manager/create[/]',
            'defaults' => [
                'controller' => TransportManagerDetailsDetailController::class,
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
                'controller' => \Olcs\Controller\SplitScreenController::class,
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
    [
        'overview' => [
            'type' => 'segment',
            'options' => [
                'route' => '',
                'defaults' => [
                    'controller' => \Olcs\Controller\Lva\Licence\OverviewController::class,
                    'action' => 'index'
                ]
            ]
        ],
        'variation' => [
            'type' => 'segment',
            'options' => [
                'route' => 'variation[/:redirectRoute][/]',
                'defaults' => [
                    'controller' => 'LvaLicence/Variation',
                    'action' => 'index'
                ]
            ]
        ]
    ]
);

$routes['lva-variation']['child_routes'] = array_merge(
    $routes['lva-variation']['child_routes'],
    [
        'review' => [
            'type' => 'segment',
            'options' => [
                'route' => 'review[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Review',
                    'action' => 'index'
                ]
            ]
        ],
        'interim' => [
            'type' => 'segment',
            'options' => [
                'route' => 'interim[/:action][/]',
                'defaults' => [
                    'controller' => 'InterimVariationController',
                    'action' => 'index'
                ]
            ]
        ],
        'grant' => [
            'type' => 'segment',
            'options' => [
                'route' => 'grant[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Grant',
                    'action' => 'grant'
                ]
            ]
        ],
        'withdraw' => [
            'type' => 'segment',
            'options' => [
                'route' => 'withdraw[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Withdraw',
                    'action' => 'index'
                ]
            ]
        ],
        'refuse' => [
            'type' => 'segment',
            'options' => [
                'route' => 'refuse[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Refuse',
                    'action' => 'index'
                ]
            ]
        ],
        'revive-application' => [
            'type' => 'segment',
            'options' => [
                'route' => 'revive-application[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Revive',
                    'action' => 'index'
                ]
            ]
        ],
        'approve-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'approve-schedule-41[/]',
                'defaults' => [
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'approveSchedule41'
                ]
            ]
        ],
        'reset-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'reset-schedule-41[/]',
                'defaults' => [
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'resetSchedule41'
                ]
            ]
        ],
        'refuse-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'refuse-schedule-41[/]',
                'defaults' => [
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'refuseSchedule41'
                ]
            ]
        ],
        'schedule41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'schedule41[/]',
                'defaults' => [
                    'controller' => 'VariationSchedule41Controller',
                    'action' => 'licenceSearch'
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'transfer' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'transfer[/:licNo][/]',
                        'defaults' => [
                            'controller' => 'VariationSchedule41Controller',
                            'action' => 'transfer'
                        ]
                    ]
                ]
            ]
        ],
        'publish' => [
            'type' => 'segment',
            'options' => [
                'route' => 'publish[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Publish',
                    'action' => 'index'
                ]
            ]
        ],
        'submit' => [
            'type' => 'segment',
            'options' => [
                'route' => 'submit[/]',
                'defaults' => [
                    'controller' => 'LvaVariation/Submit',
                    'action' => 'index'
                ]
            ]
        ],
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
    ]
);

$routes['lva-application']['child_routes'] = array_merge(
    $routes['lva-application']['child_routes'],
    [
        'review' => [
            'type' => 'segment',
            'options' => [
                'route' => 'review[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Review',
                    'action' => 'index'
                ]
            ]
        ],
        'grant' => [
            'type' => 'segment',
            'options' => [
                'route' => 'grant[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Grant',
                    'action' => 'grant'
                ]
            ]
        ],
        'undo-grant' => [
            'type' => 'segment',
            'options' => [
                'route' => 'undo-grant[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationController::class,
                    'action' => 'undoGrant'
                ]
            ]
        ],
        'not-taken-up' => [
            'type' => 'segment',
            'options' => [
                'route' => 'not-taken-up[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/NotTakenUp',
                    'action' => 'index'
                ]
            ]
        ],
        'revive-application' => [
            'type' => 'segment',
            'options' => [
                'route' => 'revive-application[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/ReviveApplication',
                    'action' => 'index'
                ]
            ]
        ],
        'withdraw' => [
            'type' => 'segment',
            'options' => [
                'route' => 'withdraw[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Withdraw',
                    'action' => 'index'
                ]
            ]
        ],
        'refuse' => [
            'type' => 'segment',
            'options' => [
                'route' => 'refuse[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Refuse',
                    'action' => 'index'
                ]
            ]
        ],
        'submit' => [
            'type' => 'segment',
            'options' => [
                'route' => 'submit[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Submit',
                    'action' => 'index'
                ]
            ]
        ],
        'schedule41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'schedule41[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationSchedule41Controller::class,
                    'action' => 'licenceSearch'
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'transfer' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'transfer[/:licNo][/]',
                        'defaults' => [
                            'controller' => ApplicationControllers\ApplicationSchedule41Controller::class,
                            'action' => 'transfer'
                        ]
                    ]
                ]
            ]
        ],
        'approve-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'approve-schedule-41[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationSchedule41Controller::class,
                    'action' => 'approveSchedule41'
                ]
            ]
        ],
        'reset-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'reset-schedule-41[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationSchedule41Controller::class,
                    'action' => 'resetSchedule41'
                ]
            ]
        ],
        'refuse-schedule-41' => [
            'type' => 'segment',
            'options' => [
                'route' => 'refuse-schedule-41[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationSchedule41Controller::class,
                    'action' => 'refuseSchedule41'
                ]
            ]
        ],
        'overview' => [
            'type' => 'segment',
            'options' => [
                'route' => '',
                'defaults' => [
                    'controller' => 'LvaApplication',
                    'action' => 'index'
                ]
            ]
        ],
        'change-of-entity' => [
            'type' => 'segment',
            'options' => [
                'route' => 'change-of-entity[/:changeId][/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationController::class,
                    'action' => 'changeOfEntity'
                ]
            ]
        ],
        'case' => [
            'type' => 'segment',
            'options' => [
                'route' => 'case[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationController::class,
                    'action' => 'case'
                ]
            ]
        ],
        'opposition' => [
            'type' => 'segment',
            'options' => [
                'route' => 'opposition[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\ApplicationController::class,
                    'action' => 'opposition'
                ]
            ]
        ],
        'documents' => [
            'type' => 'segment',
            'options' => [
                'route' => 'documents[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\Docs\ApplicationDocsController::class,
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
                            'controller' => \Olcs\Controller\Document\DocumentGenerationController::class,
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
                            'controller' => \Olcs\Controller\Document\DocumentFinaliseController::class,
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
                            'controller' => DocumentControllers\DocumentUploadController::class,
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
                            'controller' => ApplicationControllers\Docs\ApplicationDocsController::class,
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
                            'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
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
                    'controller' => ApplicationControllers\Processing\ApplicationProcessingOverviewController::class,
                    'action' => 'index'
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'publications' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'publications[/:action][/:id][/]',
                        'defaults' => [
                            'controller' => ApplicationProcessingPublicationsController::class,
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
                            'controller' => ApplicationControllers\Processing\ApplicationProcessingTasksController::class,
                            'action' => 'index'
                        ]
                    ]
                ],
                'inspection-request' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'inspection-request[/:action[/:id]][/]',
                        'defaults' => [
                            'controller' => ApplicationProcessingInspectionRequestController::class,
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
                            'controller' => Olcs\Controller\Application\Processing\HistoryController::class,
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
                            'controller' => Olcs\Controller\Application\Processing\ReadHistoryController::class,
                            'action' => 'index'
                        ]
                    ],
                ],
            ],
        ],
        'fees' => [
            'type' => 'segment',
            'options' => [
                'route' => 'fees[/]',
                'defaults' => [
                    'controller' => ApplicationControllers\Fees\ApplicationFeesController::class,
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
        'conversation' => [
            'type' => 'segment',
            'options' => [
                'route' => 'conversation[/]',
                'verb' => 'GET',
                'defaults' => [
                    'controller' => Olcs\Controller\Messages\ApplicationConversationListController::class,
                    'action' => 'index',
                    'type' => 'application',
                ],
            ],
            'may_terminate' => true,
            'child_routes' => [
                'view' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => ':conversation[/]',
                        'verb' => 'GET',
                        'defaults' => [
                            'controller' =>  Olcs\Controller\Messages\ApplicationConversationMessagesController::class,
                            'action' => 'index'
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'fileuploads' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'fileuploads/',
                        'defaults' => [
                            'controller' => \Olcs\Controller\Messages\EnableDisableFileUploadController::class,
                        ],
                    ],
                    'may_terminate' => false,
                    'child_routes' => [
                        'enable' => [
                            'type' => 'segment',
                            'options' => [
                                'route' => 'enable[/]',
                                'defaults' => [
                                    'action' => 'enable',
                                ],
                            ],
                            'may_terminate' => true,
                        ],
                        'disable' => [
                            'type' => 'segment',
                            'options' => [
                                'route' => 'disable[/]',
                                'defaults' => [
                                    'action' => 'disable',
                                ],
                            ],
                            'may_terminate' => true,
                        ],
                    ],
                ],
                'new' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'new[/]',
                        'verb' => 'GET',
                        'defaults' => [
                            'controller' => Olcs\Controller\Messages\ApplicationCreateConversationController::class,
                            'action' => 'add'
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'close' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => ':conversation/close[/]',
                        'defaults' => [
                            'controller' => Olcs\Controller\Messages\ApplicationCloseConversationController::class,
                            'action' => 'confirm'
                        ],
                    ],
                    'may_terminate' => true,
                ],
                'disable' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'disable[/]',
                        'verb' => 'GET',
                        'defaults' => [
                            'controller' => Olcs\Controller\Messages\ApplicationEnableDisableMessagingController::class,
                            'action' => 'index',
                            'type' => 'disable',
                        ]
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'popup' => [
                            'type' => 'segment',
                            'options' => [
                                'route' => 'popup[/]',
                                'verb' => 'POST',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Messages\ApplicationEnableDisableMessagingController::class,
                                    'action' => 'popup',
                                    'type' => 'disable',
                                ],
                            ],
                            'may_terminate' => true,
                        ],
                    ],
                ],
                'enable' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => 'enable[/]',
                        'verb' => 'GET',
                        'defaults' => [
                            'controller' => Olcs\Controller\Messages\ApplicationEnableDisableMessagingController::class,
                            'action' => 'index',
                            'type' => 'enable',
                        ]
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'popup' => [
                            'type' => 'segment',
                            'options' => [
                                'route' => 'popup[/]',
                                'verb' => 'POST',
                                'defaults' => [
                                    'controller' => Olcs\Controller\Messages\ApplicationEnableDisableMessagingController::class,
                                    'action' => 'popup',
                                    'type' => 'enable',
                                ],
                            ],
                            'may_terminate' => true,
                        ],
                    ],
                ],
            ],
        ],
        'interim' => [
            'type' => 'segment',
            'options' => [
                'route' => 'interim[/:action][/]',
                'defaults' => [
                    'controller' => 'InterimApplicationController',
                    'action' => 'index'
                ]
            ]
        ],
        'publish' => [
            'type' => 'segment',
            'options' => [
                'route' => 'publish[/]',
                'defaults' => [
                    'controller' => 'LvaApplication/Publish',
                    'action' => 'index'
                ]
            ]
        ],
    ]
);

/**
 * To make it easier to locate resources/controllers,
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
    __DIR__  . '/routes/licence/surrender.php',
    __DIR__  . '/routes/auth.php',
];

// Merge all module config options
foreach ($routeConfigs as $routeConfig) {
    $routes = \Laminas\Stdlib\ArrayUtils::merge($routes, include $routeConfig);
}

return $routes;
