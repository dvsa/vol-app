<?php

use Olcs\Controller\Cases;
use Olcs\Controller\Cases as CaseeControllers;

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
                'controller' => Cases\Overview\OverviewController::class,
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
                'controller' => Cases\Overview\OverviewController::class,
                'action' => 'add'
            ]
        ]
    ],
    'case_conversation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/conversation[/]',
            'verb' => 'GET',
            'defaults' => [
                'controller' => Olcs\Controller\Messages\CaseConversationListController::class,
                'action' => 'index',
                'type' => 'case',
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
                        'controller' => Olcs\Controller\Messages\CaseConversationMessagesController::class,
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
                        'controller' => Olcs\Controller\Messages\CaseCreateConversationController::class,
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
                        'controller' => Olcs\Controller\Messages\CaseCloseConversationController::class,
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
                        'controller' => Olcs\Controller\Messages\CaseEnableDisableMessagingController::class,
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
                                'controller' => Olcs\Controller\Messages\CaseEnableDisableMessagingController::class,
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
                        'controller' => Olcs\Controller\Messages\CaseEnableDisableMessagingController::class,
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
                                'controller' => Olcs\Controller\Messages\CaseEnableDisableMessagingController::class,
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
                'controller' => Cases\Opposition\OppositionController::class,
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
                'controller' => Cases\Statement\StatementController::class,
                'action' => 'index',
            ]
        ]
    ],
    'conviction_ajax' => [
        'type' => 'segment',
        'options' => [
            'route' => '/ajax/convictions/categories[/]',
            'defaults' => [
                'controller' => Cases\Conviction\ConvictionController::class,
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
                'controller' => Cases\Hearing\HearingAppealController::class,
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
                'controller' => Cases\Hearing\AppealController::class,
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
                'controller' => Cases\Hearing\StayController::class,
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
                'controller' => Olcs\Controller\Cases\AnnualTestHistory\AnnualTestHistoryController::class,
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
                'controller' => Olcs\Controller\Cases\Prohibition\ProhibitionController::class,
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
                'controller' =>  Olcs\Controller\Cases\Prohibition\ProhibitionDefectController::class,
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
                'controller' => Cases\Conviction\ConvictionController::class,
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
                'controller' => Olcs\Controller\Cases\Conviction\LegacyOffenceController::class,
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
                'controller' => Olcs\Controller\Cases\Penalty\SiController::class,
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
                'controller' =>  Olcs\Controller\Cases\Penalty\PenaltyController::class,
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
                'controller' => Olcs\Controller\Cases\Complaint\ComplaintController::class,
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
                'controller' => Olcs\Controller\Cases\Complaint\EnvironmentalComplaintController::class,
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
                'controller' => Olcs\Controller\Cases\NonPublicInquiry\NonPublicInquiryController::class,
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
                'controller' => Cases\PublicInquiry\PiController::class,
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
                'controller' => Cases\PublicInquiry\PiController::class
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
                'controller' => Cases\PublicInquiry\HearingController::class,
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
                'controller' => Cases\PublicInquiry\PiController::class,
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
                'controller' => Cases\PublicInquiry\PiController::class,
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
                'controller' => Cases\Submission\RecommendationController::class,
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
                'controller' => Olcs\Controller\Cases\Submission\DecisionController::class,
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
                'action' => '(index|add|edit|details|close|reopen|delete|print|snapshot)'
            ],
            'defaults' => [
                'controller' => Cases\Submission\SubmissionController::class,
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
                'controller' => Cases\Submission\SubmissionController::class,
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
                'controller' => Cases\Submission\ProcessSubmissionController::class,
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
                'controller' => Cases\Submission\SubmissionSectionCommentController::class,
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
                'controller' => Olcs\Controller\Cases\Processing\DecisionsController::class,
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
                'controller' => Olcs\Controller\Cases\Processing\DecisionsController::class,
                'action' => 'details'
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'repute-not-lost' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'repute-not-lost/:action[/:id][/]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => Olcs\Controller\Cases\Processing\DecisionsReputeNotLostController::class
                    ]
                ],
            ],
            'declare-unfit' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'declare-unfit/:action[/:id][/]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => Olcs\Controller\Cases\Processing\DecisionsDeclareUnfitController::class,
                    ]
                ],
            ],
            'no-further-action' => [
                'type' => 'segment',
                'options' => [
                    'route' => 'no-further-action/:action[/:id][/]',
                    'constraints' => [
                        'action' => '(add|edit|delete)',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => Olcs\Controller\Cases\Processing\DecisionsNoFurtherActionController::class
                    ]
                ],
            ],
        ],
    ],
    'processing_in_office_revocation_sla' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/in-office-revocation/sla/:action[/]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => 'edit'
            ],
            'defaults' => [
                'controller' => Olcs\Controller\Sla\RevocationsSlaController::class,
            ]
        ]
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
                'controller' => Olcs\Controller\Cases\Processing\RevokeController::class,
                'action' => 'index'
            ]
        ]
    ],

    'processing_history' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/history[/:action[/:id]][/]',
            'defaults' => [
                'controller' => Olcs\Controller\Cases\Processing\HistoryController::class,
                'action' => 'index'
            ]
        ]
    ],
    'processing_read_history' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/read-history[/]',
            'defaults' => [
                'controller' => Olcs\Controller\Cases\Processing\ReadHistoryController::class,
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
                'controller' => CaseeControllers\Processing\TaskController::class,
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
                'controller' => Cases\Processing\NoteController::class,
                'action' => 'index'
            ]
        ],
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
                'controller' => Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingController::class,
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
                'controller' => Olcs\Controller\Cases\Impounding\ImpoundingController::class,
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
                'controller' => Olcs\Controller\Cases\Processing\RevokeController::class
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
                'controller' => Olcs\Controller\Cases\Docs\CaseDocsController::class,
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
                        'controller' => Olcs\Controller\Sla\CaseDocumentSlaTargetDateController::class,
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
                        'controller' => Olcs\Controller\Sla\CaseDocumentSlaTargetDateController::class,
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
                        'type' => 'case',
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
                        'type' => 'case',
                        'controller' => \Olcs\Controller\Document\DocumentUploadController::class,
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
                        'controller' => Olcs\Controller\Cases\Docs\CaseDocsController::class,
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
                        'controller' => \Olcs\Controller\Document\DocumentRelinkController::class,
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
                                'type' => 'case',
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
                                'type' => 'case',
                                'controller' => \Olcs\Controller\Document\DocumentUploadController::class,
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
                                'controller' => Olcs\Controller\Cases\Docs\CaseDocsController::class,
                                'action' => 'delete-document'
                            ]
                        ],
                    ],
                ],
            ],
        ],
    ],
];
