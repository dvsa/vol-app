<?php
use Olcs\Controller\Cases\Processing\NoteController as CaseNoteController;
use Olcs\Controller\Cases\Hearing\AppealController as CaseAppealController;
use Olcs\Controller\Cases\Hearing\StayController as CaseStayController;
use Olcs\Controller\Cases\Submission\ProcessSubmissionController;
use Olcs\Controller\Cases\Submission\RecommendationController;

use Olcs\Controller\Cases\Submission\SubmissionController;
use Olcs\Controller\Cases\PublicInquiry\HearingController;
use Olcs\Controller\Cases\Hearing\HearingAppealController;
use Olcs\Controller\Cases\Opposition\OppositionController;
use Olcs\Controller\Cases\Statement\StatementController;
use Olcs\Controller\Cases\Overview\OverviewController;
use Olcs\Controller\Cases\PublicInquiry\PiController;

/**
 * @internal as we work on each controller, replace string with controller class
 * Routes for the licence section and case route.
 */
return [
    'case' => [
        'type' => 'segment',
        'options' => [
            'route' =>
                '/case/:action[/:case][/licence/:licence][/transportManager/:transportManager]' .
                '[/application/:application][/]',
            'constraints' => [
                'case' => '|[0-9]+',
                'action' => '(add|edit|details|redirect|delete|close|reopen)',
                'licence' => '|[0-9]+',
                'transportManager' => '|[0-9]+',
                'application' => '|[0-9]+'
            ],
            'defaults' => [
                'controller' => OverviewController::class,
                'action' => 'details'
            ],
        ],
        'may_terminate' => true
    ],
    'case_add_licence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/case/add/:licence[/]',
            'constraints' => [
                'licence' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => OverviewController::class,
                'action' => 'add'
            ]
        ]
    ],
    'case_opposition' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/application[/:application]/opposition[/:action][/:opposition][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'application' => '[0-9]+',
                'action' => '(index|add|edit|delete|generate)',
                'opposition' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => OppositionController::class,
                'action' => 'index',
            ]
        ]
    ],
    'case_statement' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/statement[/:action][/:statement][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'statement' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => StatementController::class,
                'action' => 'index',
            ]
        ]
    ],
    'conviction_ajax' => [
        'type' => 'segment',
        'options' => [
            'route' => '/ajax/convictions/categories[/]',
            'defaults' => [
                'controller' => 'CaseConvictionController',
                'action' => 'categories',
            ]
        ]
    ],
    'case_hearing_appeal' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/hearing-appeal[/:action][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => 'index',
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
            'route' => '/case/:case/appeal[/:action][/:appeal][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
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
            'route' => '/case/:case/stay[/:action][/:stayType][/:stay][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
                'stayType' => '(stay_t_tc|stay_t_ut)',
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
            'route' => '/case/:case/annual-test-history[/]',
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
            'route' => '/case/:case/prohibition[/:action][/:prohibition][/]',
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
            'route' => '/case/:case/prohibition[/:prohibition]/defect[/:action][/:id][/]',
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
            'route' => '/case/:case/conviction[/:action][/:conviction][/]',
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
    'offence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/offence[/:action/:id][/]',
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
            'route' => '/case/:case/serious-infringement[/:action][/:id][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit|delete|send)',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseSiController',
                'action' => 'index'
            ]
        ]
    ],
    'case_penalty_applied' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/serious-infringement/:si/penalty[/:action][/:id][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'si' => '[0-9]+',
                'id' => '[0-9]+',
                'action' => '(add|edit|delete)'
            ],
            'defaults' => [
                'controller' => 'CasePenaltyController',
                'action' => 'index'
            ]
        ]
    ],
    'case_complaint' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/complaint[/:action][/:complaint][/]',
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
            'route' => '/case/:case/environmental-complaint[/:action][/:complaint][/]',
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
            'route' => '/case/:case/non-pi/:action[/:id][/]',
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
            'route' => '/case/:case/pi[/:action][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(close|reopen|index|details)',
            ],
            'defaults' => [
                'controller' => PiController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_pi_agreed' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/agreed[/:action][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)'
            ],
            'defaults' => [
                'controller' => PiController::class
            ]
        ]
    ],
    'case_pi_hearing' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/:pi/hearing[/:action][/:id][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'pi' => '[0-9]+',
                'action' => '(add|edit|index|generate)',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => HearingController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_pi_decision' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/decision[/]',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => PiController::class,
                'action' => 'decision'
            ]
        ]
    ],
    'case_pi_sla' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/sla[/]',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => PiController::class,
                'action' => 'sla'
            ]
        ]
    ],
    'submission_action_recommendation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/action/recommendation[/:action[/:id]][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(add|edit)',
                'submission' => '[0-9]+',
                'id' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => RecommendationController::class,
                'action' => 'add'
            ]
        ]
    ],
    'submission_action_decision' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/action/decision[/:action[/:id]][/]',
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
            'route' => '/case/:case/submission/:action[/:submission][/:section][/:rowId][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'action' => '(index|add|edit|details|close|reopen|delete|print)'
            ],
            'defaults' => [
                'controller' => SubmissionController::class,
                'action' => 'index'
            ]
        ]
    ],
    'submission_update_table' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/update-table/:section[/:subSection][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'action' => '(index|add|edit|details|update-table)'
            ],
            'defaults' => [
                'controller' => SubmissionController::class,
                'action' => 'update-table'
            ]
        ]
    ],
    'submission_process' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission/:submission/:action[/:section][/]',
            'constraints' => [
                'case' => '[0-9]+',
                'submission' => '[0-9]+',
                'section' => '[a-z\-]+',
                'action' => '(assign|attach|information-complete)'
            ],
            'defaults' => [
                'controller' => ProcessSubmissionController::class,
            ]
        ]
    ],
    'submission_section_comment' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/submission[/:submission]/section/:submissionSection/comment/:action[/:id][/]',
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
            'route' => '/case/:case/processing[/]',
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
            'route' => '/case/:case/processing/decisions[/]',
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
                    'route' => '/repute-not-lost/:action[/:id][/]',
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
                    'route' => '/declare-unfit/:action[/:id][/]',
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
                    'route' => '/no-further-action/:action[/:id][/]',
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
            'route' => '/case/:case/processing/in-office-revocation[/:action][/]',
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
            'route' => '/case/:case/processing/history[/:action[/:id]][/]',
            'defaults' => [
                'controller' => 'CaseHistoryController',
                'action' => 'index'
            ]
        ]
    ],
    'processing_read_history' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/read-history[/]',
            'defaults' => [
                'controller' => 'CaseReadHistoryController',
                'action' => 'index'
            ]
        ]
    ],
    'case_processing_tasks' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/tasks[/:action][/]',
            'constraints' => [
                'action' => '(index|add|edit|details|overview)'
            ],
            'defaults' => [
                'controller' => Olcs\Controller\Cases\Processing\TaskController::class,
                'action' => 'index'
            ]
        ]
    ],
    'case_processing_notes' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/notes[/:action[/:id]][/]',
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
            'route' => '/licence/:licence[/case/:case][/:type/:typeId][/:section]/note[/:action][/:id][/]',
            'defaults' => [
                'controller' => 'SubmissionNoteController',
            ]
        ]
    ],
    'case_conditions_undertakings' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/conditions-undertakings[/:action[/:id]][/]',
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
            'route' => '/case[/:case]/impounding[/:action][/:impounding][/]',
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
            'route' => '/licence/:licence/case/:case/task/revoke[/:action][/:id][/]',
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
            'route' => '/case[/:case]/documents[/]',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseDocsController',
                'action' => 'documents'
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
                        'controller' => 'CaseDocumentSlaTargetDateController',
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
                        'controller' => 'CaseDocumentSlaTargetDateController',
                        'action' => 'editSla'
                    ]
                ],
            ],
            'generate' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'generate[/:doc][/]',
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
                    'route' => 'finalise/:doc[/:action][/]',
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
                    'route' => 'upload[/]',
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
                    'route' => 'delete/:doc[/]',
                    'defaults' => [
                        'type' => 'case',
                        'controller' => 'CaseDocsController',
                        'action' => 'delete-document'
                    ]
                ],
            ],
            'relink' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'relink/:doc[/]',
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
                    'route' => ':entityType/:entityId[/]',
                    'constraints' => [
                        'entityType' => '(statement|hearing|opposition|complaint|impounding)',
                        'entityId' => '[0-9]+'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'generate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'generate[/:doc][/]',
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
                            'route' => 'finalise/:doc[/:action][/]',
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
                            'route' => 'upload[/]',
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
                            'route' => 'delete/:doc[/]',
                            'defaults' => [
                                'type' => 'case',
                                'controller' => 'CaseDocsController',
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ],
];
