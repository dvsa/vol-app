<?php

use Olcs\Controller\Cases\Hearing\HearingAppealController as HearingAppealController;
use Olcs\Controller\Cases\Hearing\AppealController as CaseAppealController;
use Olcs\Controller\Cases\Hearing\StayController as CaseStayController;

use Olcs\Controller\Cases\Processing\NoteController as CaseNoteController;
use Olcs\Controller\Application\Processing\ApplicationProcessingNoteController as ApplicationProcessingNoteController;
use Olcs\Controller\Bus\Processing\BusProcessingNoteController as BusProcessingNoteController;
use Olcs\Controller\Licence\Processing\LicenceProcessingNoteController as LicenceProcessingNoteController;
use Olcs\Controller\Operator\OperatorProcessingNoteController as OperatorProcessingNoteController;
use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController as TMProcessingNoteController;

use Olcs\Controller\SearchController as SearchController;

$routes = [
    'dashboard' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/',
            'defaults' => [
                'controller' => 'IndexController',
                'action' => 'index',
            ]
        ]
    ],
    'operators' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/search2/operators',
            'defaults' => [
                'controller' => 'SearchController',
                'action' => 'operator'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'operators-params' => [
                'type' => 'wildcard',
                'options' => [
                    'key_value_delimiter' => '/',
                    'param_delimiter' => '/',
                    'defaults' => [
                        'page' => 1,
                        'limit' => 10
                    ]
                ]
            ]
        ]
    ],
    'search' => [
        'type' => 'segment',
        'options' => [
            'route' => '/search[/:index[/:action][/:child_id]]',
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
            'route' => '/task[/:action][/:task][/type/:type/:typeId]',
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
    // These routes are for the licence page
    'case' => [
        'type' => 'segment',
        'options' => [
            'route' =>
            '/case/:action[/:case][/licence/:licence][/transportManager/:transportManager][/application/:application]',
            'constraints' => [
                'case' => '|[0-9]+',
                'action' => '(add|edit|details|redirect|delete|close|reopen)',
                'licence' => '|[0-9]+',
                'transportManager' => '|[0-9]+',
                'application' => '|[0-9]+'
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\Overview\OverviewController::class,
                'action' => 'details'
            ],
        ],
        'may_terminate' => true
    ],
    'case_add_licence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/case/add/:licence',
            'constraints' => [
                'licence' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\Overview\OverviewController::class,
                'action' => 'add'
            ]
        ]
    ],
    'case_opposition' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/application[/:application]/opposition[/:action][/:opposition]',
            'constraints' => [
                'case' => '[0-9]+',
                'application' => '[0-9]+',
                'action' => '(index|add|edit|delete|generate)',
                'opposition' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseOppositionController',
                'action' => 'index',
            ]
        ]
    ],
    'case_statement' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/statement[/:action][/:statement]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'statement' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseStatementController',
                'action' => 'index',
            ]
        ]
    ],
    'conviction_ajax' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/ajax/convictions/categories',
            'defaults' => [
                'controller' => 'CaseConvictionController',
                'action' => 'categories',
            ]
        ]
    ],
    'case_hearing_appeal' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/hearing-appeal[/:action]',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => HearingAppealController::class,
                'action' => 'details'
            ]
        ]
    ],
    'case_appeal' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/appeal[/:action][/:appeal]',
            'constraints' => [
                'case' => '[0-9]+',
                'appeal' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => CaseAppealController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_stay' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/stay[/:action][/:stayType][/:stay]',
            'constraints' => [
                'case' => '[0-9]+',
                'stay' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => CaseStayController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_annual_test_history' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/annual-test-history',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseAnnualTestHistoryController',
                'action' => 'edit'
            ]
        ]
    ],
    'case_prohibition' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/prohibition[/:action][/:prohibition]',
            'constraints' => [
                'case' => '[0-9]+',
                'prohibition' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseProhibitionController',
                'action' => 'index'
            ]
        ],
        'may_terminate' => true
    ],
    'case_prohibition_defect' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/prohibition[/:prohibition]/defect[/:action][/:id]',
            'constraints' => [
                'id' => '[0-9]+',
                'prohibition' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseProhibitionDefectController',
                'action' => 'index'
            ]
        ]
    ],
    'conviction' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/conviction[/:action][/:conviction]',
            'constraints' => [
                'case' => '[0-9]+',
                'conviction' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseConvictionController',
                'action' => 'index',
            ]
        ],
    ],
    'serious_infringement' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/serious-infringement[/:action][/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'id' => '[0-9]+'

            ],
            'defaults' => [
                'controller' => 'CaseSeriousInfringementController',
                'action' => 'index',
            ]
        ],
    ],
    'offence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/offence[/:action/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseLegacyOffenceController',
                'action' => 'index'
            ]
        ]
    ],
    'case_penalty' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/penalty[/:action][/:penalty]',
            'constraints' => [
                'case' => '[0-9]+',
                'penalty' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CasePenaltyController',
                'action' => 'index'
            ]
        ],
    ],
    'case_penalty_edit' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/penalty/:seriousInfringement/:action[/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'seriousInfringement' => '[0-9]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseAppliedPenaltyController'
            ]
        ],
    ],
    'case_complaint' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/complaint[/:action][/:complaint]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'complaint' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseComplaintController',
                'action' => 'index',
                'isCompliance' => 1
            ]
        ]
    ],
    'case_environmental_complaint' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/environmental-complaint[/:action][/:complaint]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit|delete|generate)',
                'complaint' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseEnvironmentalComplaintController',
                'action' => 'index',
            ]
        ]
    ],
    'case_non_pi' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/non-pi/:action[/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit|details)',
            ],
            'defaults' => [
                'controller' => 'CaseNonPublicInquiryController',
                'action' => 'details'
            ]
        ]
    ],
    'case_pi' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(close|reopen|index)',
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\PublicInquiry\PiController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_pi_agreed' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/agreed[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)'
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\PublicInquiry\PiController::class
            ]
        ]
    ],
    'case_pi_hearing' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/:pi/hearing[/:action][/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'pi' => '[0-9]+',
                'action' => '(add|edit|index)',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'PublicInquiry\HearingController',
                'action' => 'index'
            ]
        ]
    ],
    'case_pi_decision' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/decision',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\PublicInquiry\PiController::class,
                'action' => 'decision'
            ]
        ]
    ],
    'case_pi_sla' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/sla',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => \Olcs\Controller\Cases\PublicInquiry\PiController::class,
                'action' => 'sla'
            ]
        ]
    ],
    'submission_action_recommendation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/action/recommendation[/:action[/:id]]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
                'submission' => '[0-9]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseSubmissionRecommendationController',
                'action' => 'add'
            ]
        ]
    ],
    'submission_action_decision' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/action/decision[/:action[/:id]]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
                'submission' => '[0-9]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseSubmissionDecisionController',
                'action' => 'add'
            ]
        ]
    ],
    'submission' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:action[/:submission][/:section][/:rowId]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'action' => '(index|add|edit|details|close|reopen|delete)'
            ],
            'defaults' => [
                'controller' => 'CaseSubmissionController',
                'action' => 'index'
            ]
        ]
    ],
    'submission_update_table' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/update-table/:section[/:subSection]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'action' => '(index|add|edit|details|update-table)'
            ],
            'defaults' => [
                'controller' => 'CaseSubmissionController',
                'action' => 'update-table'
            ]
        ]
    ],
    'submission_process' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/:action[/:section]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'section' => '[a-z\-]+',
                'action' => '(assign|attach)'
            ],
            'defaults' => [
                'controller' => 'CaseProcessSubmissionController',
            ]
        ]
    ],
    'submission_section_comment' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/[:submission]/section/:submissionSection/comment/:action[/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
                'submission' => '[0-9]+',
                'submissionSection' => '[a-z\-]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseSubmissionSectionCommentController',
            ]
        ]
    ],
    'processing' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseDecisionsController',
                'action' => 'index'
            ]
        ]
    ],
    'processing_decisions' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/decisions',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseDecisionsController',
                'action' => 'details'
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'repute-not-lost' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/repute-not-lost/:action[/:id]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'CaseDecisionsReputeNotLostController'
                    ]
                ],
            ],
            'declare-unfit' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/declare-unfit/:action[/:id]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'CaseDecisionsDeclareUnfitController'
                    ]
                ],
            ],
            'no-further-action' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/no-further-action/:action[/:id]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'CaseDecisionsNoFurtherActionController'
                    ]
                ],
            ],
        ],
    ],
    'processing_in_office_revocation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/in-office-revocation[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit|details)'
            ],
            'defaults' => [
                'controller' => 'CaseRevokeController',
                'action' => 'index'
            ]
        ]
    ],
    'processing_history' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/history',
            'defaults' => [
                //'controller' => 'Crud\Case\EventHistoryController',
                'controller' => 'CaseHistoryController',
                'action' => 'index'
            ]
        ]
    ],
    'case_processing_tasks' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/tasks[/:action]',
            'constraints' => [
                'action' => '(index|add|edit|details|overview)'
            ],
            'defaults' => [
                'controller' => 'CaseTaskController',
                'action' => 'index'
            ]
        ]
    ],
    'case_processing_notes' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/notes[/:action[/:id]]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => 'index|details|add|edit|delete',
            ],
            'defaults' => [
                'controller' => CaseNoteController::class,
                'action' => 'index'
            ]
        ],
    ],
    'note' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence[/case/:case][/:type/:typeId][/:section]/note[/:action][/:id]',
            'defaults' => [
                'controller' => 'SubmissionNoteController',
            ]
        ]
    ],
    'case_conditions_undertakings' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/conditions-undertakings[/:action[/:id]]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseConditionUndertakingController',
                'action' => 'index'
            ]
        ]
    ],
    'case_details_impounding' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/[:case]/impounding[/:action][/:impounding]',
            'constraints' => [
                'case' => '[0-9]+',
                'impounding' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseImpoundingController',
                'action' => 'index'
            ]
        ]
    ],
    'case_revoke' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/task/revoke[/:action][/:id]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseRevokeController'
            ]
        ]
    ],
    'case_licence_docs_attachments' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/[:case]/documents',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController',
                'action' => 'documents'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'generate' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/generate[/:doc]',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'DocumentGenerationController',
                        'action' => 'generate'
                    ]
                ],
            ],
            'finalise' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/finalise/:doc[/:action]',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'DocumentFinaliseController',
                        'action' => 'finalise'
                    ]
                ],
            ],
            'upload' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/upload',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'DocumentUploadController',
                        'action' => 'upload'
                    ]
                ],
            ],
            'delete' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/delete/:doc',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'CaseController',
                        'action' => 'delete-document'
                    ]
                ],
            ],
            'relink' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/relink/:doc',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'DocumentRelinkController',
                        'action' => 'relink'
                    ]
                ],
            ],
            'entity' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:entityType/:entityId',
                    'constraints' => [
                        'entityType' => '(statement|hearing|opposition|complaint)',
                        'entityId' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'CaseController',
                        'action' => 'documents'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/generate[/:doc]',
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'DocumentGenerationController',
                                'action' => 'generate'
                            ]
                        ],
                    ],
                    'finalise' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/finalise/:doc[/:action]',
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'DocumentFinaliseController',
                                'action' => 'finalise'
                            ]
                        ],
                    ],
                    'upload' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/upload',
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'DocumentUploadController',
                                'action' => 'upload'
                            ]
                        ],
                    ],
                    'delete' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/delete/:doc',
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'CaseController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ],
    'entity_lists' => [
        'type' => 'segment',
        'options' => [
            'route' => '/list/[:type]/[:value]',
            'defaults' => [
                'controller' => 'IndexController',
                'action' => 'entityList'
            ]
        ]
    ],
    'template_lists' => [
        'type' => 'segment',
        'options' => [
            'route' => '/list-template-bookmarks/:id',
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
            'route' => '/documents/tmp/:id/:filename',
            'defaults' => [
                'controller' => 'DocumentGenerationController',
                'action' => 'downloadTmp'
            ]
        ]
    ],
    // These routes are for the licence page
    'licence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence',
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
            'event-history' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/event-history',
                    'defaults' => [
                        //'controller' => 'Crud\Licence\EventHistoryController',
                        'controller' => 'LicenceHistoryController',
                        'action' => 'index',
                    ]
                ],
            ],
            'active-licence-check' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/active-licence-check/:decision',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'activeLicenceCheck',
                    ]
                ],
            ],
            'curtail-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/curtail[/:status]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'curtail'
                    ]
                ],
            ],
            'revoke-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/revoke[/:status]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'revoke',
                    ]
                ],
            ],
            'suspend-licence' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/suspend[/:status]',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'suspend',
                    ]
                ],
            ],
            'surrender-licence' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/surrender',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'surrender',
                    ]
                ],
            ],
            'terminate-licence' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/terminate',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'terminate',
                    ]
                ],
            ],
            'reset-to-valid' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/reset-to-valid',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'resetToValid',
                    ]
                ],
            ],
            'undo-surrender' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/undo-surrender',
                    'defaults' => [
                        'controller' => 'LicenceDecisionsController',
                        'action' => 'resetToValid',
                        'title' => 'licence-status.undo-surrender.title',
                    ]
                ],
            ],
            'undo-terminate' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/undo-terminate',
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
                    'route' => '/grace-periods[/:action][/:child_id]',
                    'defaults' => [
                        'controller' => 'LicenceGracePeriodsController',
                        'action' => 'index',
                        'child_id' => null
                    ]
                ]
            ],
            'bus' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/bus',
                    'defaults' => [
                        'controller' => 'LicenceController',
                        'action' => 'bus',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'registration' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:action[/:id]',
                            'constraints' => [
                                'action' => '(add|edit)',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'controller' => 'BusRegistrationController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'create_variation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/variation/create/:busRegId',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => 'BusRegistrationController',
                                'action' => 'createVariation'
                            ]
                        ]
                    ],
                    'create_cancellation' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/cancellation/create/:busRegId',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => 'BusRegistrationController',
                                'action' => 'createCancellation'
                            ]
                        ]
                    ],
                    'request_map' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/request-map/:busRegId',
                            'defaults' => [
                                'constraints' => [
                                    'busRegId' => '[0-9]+',
                                ],
                                'controller' => 'BusRequestMapController',
                                'action' => 'requestMap'
                            ]
                        ]
                    ],
                ]
            ],
            'bus-details' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/details',
                    'defaults' => [
                        'controller' => 'BusDetailsController',
                        'action' => 'service',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'service' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/service',
                            'defaults' => [
                                'controller' => 'BusDetailsController',
                                'action' => 'service',
                            ]
                        ],
                    ],
                    'stop' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/stop',
                            'defaults' => [
                                'controller' => 'BusDetailsController',
                                'action' => 'stop',
                            ]
                        ],
                    ],
                    'ta' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/ta',
                            'defaults' => [
                                'controller' => 'BusDetailsController',
                                'action' => 'ta',
                            ]
                        ],
                    ],
                    'quality' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/quality',
                            'defaults' => [
                                'controller' => 'BusDetailsController',
                                'action' => 'quality',
                            ]
                        ],
                    ]
                ]
            ],
            'bus-short' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/short',
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
                    'route' => '/bus/:busRegId/register-service',
                    'defaults' => [
                        'controller' => 'BusServiceController',
                        'action' => 'edit',
                    ]
                ],
                'may_terminate' => true
            ],
            'bus-docs' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/docs',
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
                            'route' => '/generate[/:doc]',
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
                            'route' => '/finalise/:doc[/:action]',
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
                            'route' => '/upload',
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
                            'route' => '/delete/:doc',
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
                            'route' => '/relink/:doc',
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
                    'route' => '/bus/:busRegId/processing',
                    'defaults' => [
                        'controller' => 'BusProcessingDecisionController',
                        'action' => 'details',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'decisions' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/decisions[/:action]',
                            'constraints' => [
                                'action' => '(cancel|grant|refuse-by-short-notice|refuse|republish|reset|withdraw)'
                            ],
                            'defaults' => [
                                'controller' => 'BusProcessingDecisionController',
                                'action' => 'details'
                            ]
                        ],
                    ],
                    'registration-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/registration-history[/:action]',
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
                            'route' => '/notes[/:action[/:id]]',
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
                            'route' => '/tasks',
                            'defaults' => [
                                'controller' => 'BusProcessingTaskController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'event-history' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/event-history',
                            'defaults' => [
                                'controller' => 'BusRegHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/fees',
                    'defaults' => [
                        'controller' => 'BusFeesController',
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:action/:fee',
                            'constraints' => [
                                'fee' => '([0-9]+,?)+',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ]
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/cases',
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
                    'route' => '/opposition',
                    'defaults' => [
                        'action' => 'opposition',
                    ]
                ],
                'may_terminate' => true,
            ],
            'documents' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/documents',
                    'defaults' => [
                        'action' => 'documents',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/generate[/:doc]',
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
                            'route' => '/finalise/:doc[/:action]',
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
                            'route' => '/upload',
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
                            'route' => '/delete/:doc',
                            'defaults' => [
                                'type' => 'licence',
                                'controller' => 'LicenceController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/relink/:doc',
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
                'type' => 'literal',
                'options' => [
                    'route' => '/processing',
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
                            'route' => '/publications[/:action][/:id]',
                            'defaults' => [
                                'controller' => 'LicenceProcessingPublicationsController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'tasks' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/tasks',
                            'defaults' => [
                                'controller' => 'LicenceProcessingTasksController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'notes' => [ // Licence Notes
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes[/:action[/:id]]',
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
                            'route' => '/inspection-request[/:action[/:id]]',
                            'defaults' => [
                                'controller' => 'LicenceProcessingInspectionRequestController',
                                'action' => 'index'
                            ]
                        ],
                    ]
                ]
            ],
            'fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/fees',
                    'defaults' => [
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:action/:fee',
                            'constraints' => [
                                'fee' => '([0-9]+,?)+',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ]
            ],
            'update-continuation' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/update-continuation',
                    'defaults' => [
                        'controller' => 'ContinuationController',
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
            'route' => '/operator/:organisation',
            'constraints' => [
                'organisation' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'OperatorController',
                'action' => 'index-jump',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'business-details' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/business-details',
                    'defaults' => [
                        'controller' => 'OperatorBusinessDetailsController',
                        'action' => 'index',
                    ]
                ]
            ],
            'people' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/people[/:action][/:id]',
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
            'licences-applications' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/licences-applications',
                    'defaults' => [
                        'controller' => 'OperatorLicencesApplicationsController',
                        'action' => 'index',
                    ]
                ]
            ],
            'new-application' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/new-application',
                    'defaults' => [
                        'controller' => 'OperatorController',
                        'action' => 'newApplication',
                    ]
                ]
            ],
            'irfo' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/irfo',
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
                            'route' => '/details',
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
                            'route' => '/gv-permits[/:action][/:id]',
                            'constraints' => [
                                'action' => '(add|details|reset|approve)',
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
                            'route' => '/psv-authorisations[/:action][/:id]',
                            'constraints' => [
                                'action' => '(add|edit)',
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
                    'route' => '/processing',
                    'defaults' => array(
                        'controller' => 'OperatorHistoryController',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => [
                    'history' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/history',
                            'defaults' => [
                                'controller' => 'OperatorHistoryController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'notes' => [
                        'type' => 'segment',
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/notes[/:action[/:id]]',
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
                            'route' => '/tasks',
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
                    'route' => '/fees',
                    'defaults' => [
                        'controller' => 'OperatorFeesController',
                        'action' => 'fees',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'fee_action' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:action/:fee',
                            'constraints' => [
                                'fee' => '([0-9]+,?)+',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ]
            ],
            'documents' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/documents',
                    'defaults' => [
                        'action' => 'documents',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/generate[/:doc]',
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
                            'route' => '/finalise/:doc[/:action]',
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
                            'route' => '/upload',
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
                            'route' => '/delete/:doc',
                            'defaults' => [
                                'type' => 'irfoOrganisation',
                                'controller' => 'OperatorController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                    'relink' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/relink/:doc',
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
                'type' => 'literal',
                'options' => [
                    'route' => '/disqualify',
                    'defaults' => [
                        'controller' => Olcs\Controller\DisqualifyController::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'disqualify_person' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/person/:person/disqualify',
                    'defaults' => [
                        'controller' => Olcs\Controller\DisqualifyController::class,
                        'action' => 'index',
                    ]
                ],
            ],
            'merge' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/merge',
                    'defaults' => [
                        'controller' => 'OperatorController',
                        'action' => 'merge',
                    ]
                ],
            ],
        ]
    ],
    'operator-lookup' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/lookup/:organisation',
            'defaults' => [
                'controller' => 'OperatorController',
                'action' => 'lookup',
            ]
        ],
    ],
    'create_operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/create',
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
            'route' => '/operator-unlicensed/:organisation',
            'constraints' => [
                'organisation' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'UnlicensedBusinessDetailsController',
                'action' => 'index-jump',
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'business-details' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/business-details',
                    'defaults' => [
                        'controller' => 'UnlicensedBusinessDetailsController',
                        'action' => 'index',
                    ]
                ]
            ],
            'vehicles' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/vehicles[/:action[/:id]]',
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
                'type' => 'literal',
                'options' => [
                    'route' => '/cases',
                    'defaults' => [
                        'controller' => 'UnlicensedOperatorController',
                        'action' => 'cases',
                    ]
                ]
            ],
        ]
    ],
    'create_unlicensed_operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator-unlicensed/create',
            'defaults' => [
                'controller' => 'UnlicensedBusinessDetailsController',
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
            'route' => '/transport-manager/:transportManager',
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
                    'route' => '/details'
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'details' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/details',
                            'defaults' => [
                                'controller' => 'TMDetailsDetailController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true
                    ],
                    'competences' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/competences[/:action][/:id]',
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
                            'route' => '/responsibilities[/:action[/:id][/title/:title]]',
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
                            'route' => '/employment[/:action[/:id]]',
                            'defaults' => [
                                'controller' => 'TMDetailsEmploymentController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'previous-history' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/previous-history[/:action[/:id]]',
                            'defaults' => [
                                'controller' => 'TMDetailsPreviousHistoryController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                ],
            ],
            'processing' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/processing',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'index-processing-jump',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'decisions' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/decisions',
                            'defaults' => [
                                'controller' => 'TMProcessingDecisionController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'history' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/history',
                            'defaults' => [
                                'controller' => 'TMProcessingHistoryController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'publication' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/publication[/:action][/:id]',
                            'defaults' => [
                                'controller' => 'TMProcessingPublicationController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes[/:action[/:id]]',
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
                        'type' => 'literal',
                        'options' => [
                            'route' => '/tasks',
                            'defaults' => [
                                'controller' => 'TMProcessingTaskController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'event-history' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/event-history',
                            'defaults' => [
                                'controller' => 'TransportManagerHistoryController',
                                //'controller' => 'Crud\TransportManager\EventHistoryController',
                                'action' => 'index',
                            ]
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/cases[/:action][/:id]',
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
                    'route' => '/documents',
                    'defaults' => [
                        'controller' => 'TMDocumentController',
                        'action' => 'index',
                    ]
                ],
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/generate[/:doc]',
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
                            'route' => '/finalise/:doc[/:action]',
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
                            'route' => '/upload',
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
                            'route' => '/delete/:doc',
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
                            'route' => '/relink/:doc',
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
                'type' => 'literal',
                'options' => [
                    'route' => '/can-remove',
                    'defaults' => [
                        'controller' => 'TMProcessingDecisionController',
                        'action' => 'canRemove'
                    ],
                ],
            ],
            'remove' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/remove',
                    'defaults' => [
                        'controller' => 'TMProcessingDecisionController',
                        'action' => 'remove'
                    ],
                ],
            ],
            'merge' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/merge',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'merge'
                    ],
                ],
            ],
            'unmerge' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/unmerge',
                    'defaults' => [
                        'controller' => 'TMController',
                        'action' => 'unmerge'
                    ],
                ],
            ],
        ],
    ],
    'transport-manager-lookup' => [
        'type' => 'literal',
        'options' => [
            'route' => '/transport-manager/lookup',
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
            'route' => '/transport-manager/create',
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
            'route' => '/split/',
            'defaults' => [
                'controller' => 'SplitScreenController',
                'action' => 'index'
            ]
        ]
    ]
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
                        'route' => 'transfer[/:licNo]',
                        'defaults' => array(
                            'controller' => 'VariationSchedule41Controller',
                            'action' => 'transfer'
                        )
                    )
                )
            )
        )
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
                        'route' => 'transfer[/:licNo]',
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
                'route' => 'change-of-entity[/:changeId]',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'changeOfEntity'
                )
            )
        ),
        'case' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'case/',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'case'
                )
            )
        ),
        'opposition' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'opposition/',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'opposition'
                )
            )
        ),
        'documents' => [
            'type' => 'literal',
            'options' => [
                'route' => 'documents',
                'defaults' => [
                    'controller' => 'ApplicationController',
                    'action' => 'documents',
                ]
            ],
            'may_terminate' => true,
            'child_routes' => [
                'generate' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/generate[/:doc]',
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
                        'route' => '/finalise/:doc[/:action]',
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
                        'route' => '/upload',
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
                        'route' => '/delete/:doc',
                        'defaults' => [
                            'type' => 'application',
                            'controller' => 'ApplicationController',
                            'action' => 'delete-document'
                        ]
                    ],
                ],
                'relink' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/relink/:doc',
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
                'route' => 'processing',
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
                        'route' => '/publications[/:action][/:id]',
                        'defaults' => [
                            'controller' => 'ApplicationController',
                            'action' => 'publications'
                        ]
                    ],
                ],
                'tasks' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/tasks',
                        'defaults' => [
                            'controller' => 'ApplicationProcessingTasksController',
                            'action' => 'index'
                        ]
                    ]
                ],
                'inspection-request' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/inspection-request[/:action[/:id]]',
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
                        'route' => '/notes[/:action[/:id]]',
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
                        'route' => '/history',
                        'defaults' => [
                            'controller' => 'ApplicationHistoryController',
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
                    'controller' => 'ApplicationController',
                    'action' => 'fees',
                )
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'fee_action' => array(
                    'type' => 'segment',
                    'options' => array(
                        'route' => ':action/:fee',
                        'constraints' => array(
                            'fee' => '([0-9]+,?)+',
                        ),
                    ),
                    'may_terminate' => true,
                ),
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
        'undertakings' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'undertakings[/]',
                'defaults' => array(
                    'controller' => 'LvaApplication/Undertakings',
                    'action' => 'index'
                )
            )
        )
    )
);

return $routes;
