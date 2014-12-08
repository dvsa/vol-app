<?php

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
            'route' => '/search[/:index]',
            'defaults' => [
                'controller' => 'SearchController',
                'action' => 'index',
                'index' => 'licence'
            ]
        ]
    ],
    'advancedsearch' => [
        'type' => 'segment',
        'options' => [
            'route' => '/advancedsearch',
            'defaults' => [
                'controller' => 'SearchController',
                'action' => 'advanced'
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
                'action' => '[a-z]+',
                'licence' => '|[0-9]+',
                'transportManager' => '|[0-9]+',
                'application' => '|[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController',
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
                'controller' => 'CaseController',
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
                'action' => '[a-z]+',
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
                'controller' => 'CaseHearingAppealController',
                'action' => 'index'
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
                'controller' => 'CaseAppealController',
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
                'appeal' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseStayController',
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
                'action' => 'index'
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
    'offence' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/offence[/:action/:offence]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
                'offence' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseOffenceController',
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
                'action' => 'index'
            ]
        ]
    ],
    'case_pi' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(close|reopen|details)',
            ],
            'defaults' => [
                'controller' => 'CasePublicInquiryController',
                'action' => 'details'
            ]
        ]
    ],
    'case_pi_agreed' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/agreed[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+'
            ],
            'defaults' => [
                'controller' => 'PublicInquiry\AgreedAndLegislationController',
                'action' => 'index'
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
                'action' => '[a-z]+',
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
            'route' => '/case/:case/pi/decision[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
            ],
            'defaults' => [
                'controller' => 'PublicInquiry\RegisterDecisionController',
                'action' => 'index'
            ]
        ]
    ],
    'case_pi_sla' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/pi/sla[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '[a-z]+',
            ],
            'defaults' => [
                'controller' => 'PublicInquiry\SlaController',
                'action' => 'index'
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
            'route' => '/case/:case/processing[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(index|add|edit|details|overview)'
            ],
            'defaults' => [
                'controller' => 'CaseProcessingController',
                'action' => 'overview'
            ]
        ]
    ],
    'processing_decisions' => [
        'type' => 'segment',
        'options' => [
            'route' => '/case/:case/processing/decisions[/:action]',
            'constraints' => [
                'case' => '[0-9]+',
                'action' => '(index|add|edit|details|overview)'
            ],
            'defaults' => [
                'controller' => 'CaseDecisionsController'
            ]
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
            'route' => '/case/:case/processing/history[/:action]',
            'constraints' => [
                'action' => '(index|add|edit|details|overview)'
            ],
            'defaults' => [
                'controller' => 'CaseHistoryController',
                'action' => 'index'
            ]
        ]
    ],
    'processing_tasks' => [
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
            'route' => '/case/:case/processing/notes',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseNoteController',
                'action' => 'index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'add-note' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:action/:noteType[/:linkedId]',
                    'defaults' => [
                        'constraints' => [
                            'case' => '[0-9]+',
                            'noteType' => '[A-Za-z]+',
                            'linkedId' => '[0-9]+',
                        ],
                        'controller' => 'CaseNoteController',
                        'action' => 'add'
                    ]
                ]
            ],
            'modify-note' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:action[/:id]',
                    'defaults' => [
                        'constraints' => [
                            'case' => '[0-9]+',
                            'id' => '[0-9]+',
                        ],
                        'controller' => 'CaseNoteController',
                        'action' => 'edit'
                    ]
                ],
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
            'route' => '/case/[:case]/documents[/licence/:licence]',
            'constraints' => [
                'case' => '[0-9]+',
                'licence' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController',
                'action' => 'documents'
            ]
        ]
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
            ],
            'bus-details' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/details',
                    'defaults' => [
                        'controller' => 'BusDetailsController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'service' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/service',
                            'defaults' => [
                                'controller' => 'BusDetailsServiceController',
                                'action' => 'edit',
                            ]
                        ],
                    ],
                    'stop' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/stop',
                            'defaults' => [
                                'controller' => 'BusDetailsStopController',
                                'action' => 'edit',
                            ]
                        ],
                    ],
                    'ta' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/ta',
                            'defaults' => [
                                'controller' => 'BusDetailsTaController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                    'quality' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/quality',
                            'defaults' => [
                                'controller' => 'BusDetailsQualityController',
                                'action' => 'edit',
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
                'may_terminate' => true,
                'child_routes' => [
                    'placeholder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/placeholder',
                            'defaults' => [
                                'controller' => 'BusShortPlaceholderController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-route' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/route',
                    'defaults' => [
                        'controller' => 'BusRouteController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'placeholder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/placeholder',
                            'defaults' => [
                                'controller' => 'BusRoutePlaceholderController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-trc' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/trc',
                    'defaults' => [
                        'controller' => 'BusTrcController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'placeholder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/placeholder',
                            'defaults' => [
                                'controller' => 'BusTrcPlaceholderController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-docs' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/docs',
                    'defaults' => [
                        'controller' => 'BusDocsController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'placeholder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/placeholder',
                            'defaults' => [
                                'controller' => 'BusDocsPlaceholderController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'bus-processing' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/processing',
                    'defaults' => [
                        'controller' => 'BusProcessingController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes',
                            'defaults' => [
                                'controller' => 'BusProcessingNoteController',
                                'action' => 'index',
                                'page' => 1,
                                'limit' => 10,
                                'sort' => 'priority',
                                'order' => 'DESC'
                            ]
                        ],
                    ],
                    'add-note' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes/:action/:noteType[/:linkedId]',
                            'defaults' => [
                                'constraints' => [
                                    'noteType' => '[A-Za-z]+',
                                    'linkedId' => '[0-9]+',
                                ],
                                'controller' => 'BusProcessingNoteController',
                                'action' => 'add'
                            ]
                        ]
                    ],
                    'modify-note' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes/:action[/:id]',
                            'defaults' => [
                                'constraints' => [
                                    'id' => '[0-9]+',
                                ],
                                'controller' => 'BusProcessingNoteController',
                            ]
                        ]
                    ]
                ]
            ],
            'bus-fees' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/bus/:busRegId/fees',
                    'defaults' => [
                        'controller' => 'BusFeesController',
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'placeholder' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/placeholder',
                            'defaults' => [
                                'controller' => 'BusFeesPlaceholderController',
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'cases' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/cases/page/:page/limit/:limit/sort/:sort/order/:order',
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
                            'route' => '/generate[/:tmpId]',
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
                            'route' => '/finalise/:tmpId',
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
                    'notes' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes',
                            'defaults' => [
                                'controller' => 'LicenceProcessingNoteController',
                                'action' => 'index'
                            ]
                        ],
                    ],
                    'add-note' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes/:action/:noteType[/:linkedId]',
                            'defaults' => [
                                'constraints' => [
                                    'noteType' => '[A-Za-z]+',
                                    'linkedId' => '[0-9]+',
                                ],
                                'controller' => 'LicenceProcessingNoteController',
                                'action' => 'add'
                            ]
                        ]
                    ],
                    'modify-note' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/notes/:action[/:id]',
                            'defaults' => [
                                'constraints' => [
                                    'id' => '[0-9]+',
                                ],
                                'controller' => 'LicenceProcessingNoteController',
                            ]
                        ]
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
        ]
    ],
    'operator' => [
        'type' => 'segment',
        'options' => [
            'route' => '/operator/:operator',
            'constraints' => [
                'operator' => '[0-9]+'
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
                'type' => 'literal',
                'options' => [
                    'route' => '/people',
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
        ]
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
    'create_variation' => [
        'type' => 'segment',
        'options' => [
            'route' => '/variation/create/:licence',
            'defaults' => [
                'constraints' => [
                    'licence' => '[0-9]+',
                ],
                'controller' => 'LvaLicence/Overview',
                'action' => 'createVariation'
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
                'type' => 'literal',
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
                        'type' => 'literal',
                        'options' => [
                            'route' => '/competences',
                            'defaults' => [
                                'controller' => 'TMDetailsCompetenceController',
                                'action' => 'index',
                            ]
                        ]
                    ],
                    'applications-licences' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/applications-licences',
                            'defaults' => [
                                'controller' => 'TMDetailsApplicationLicenceController',
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
                    'notes' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/notes',
                            'defaults' => [
                                'controller' => 'TMProcessingNoteController',
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
                ],
            ],
            'cases' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/cases',
                    'defaults' => [
                        'controller' => 'TMCaseController',
                        'action' => 'index',
                    ]
                ]
            ],
            'documents' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/documents',
                    'defaults' => [
                        'controller' => 'TMDocumentController',
                        'action' => 'index',
                    ]
                ]
            ],
        ],
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
        )
    )
);

$routes['lva-application']['child_routes'] = array_merge(
    $routes['lva-application']['child_routes'],
    array(
        'grant' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'grant/',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'grant'
                )
            )
        ),
        'undo-grant' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'undo-grant/',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'undoGrant'
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
        'environmental' => array(
            'type' => 'segment',
            'options' => array(
                'route' => 'environmental/',
                'defaults' => array(
                    'controller' => 'ApplicationController',
                    'action' => 'environmental'
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
                        'route' => '/generate[/:tmpId]',
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
                        'route' => '/finalise/:tmpId',
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
                'notes' => [
                    'type' => 'segment',
                    'may_terminate' => true,
                    'options' => [
                        'route' => '/notes',
                        'defaults' => [
                            'controller' => 'ApplicationProcessingNoteController',
                            'action' => 'index'
                        ]
                    ],
                ],
                'add-note' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/notes/:action/:noteType[/:linkedId]',
                        'defaults' => [
                            'constraints' => [
                                'noteType' => '[A-Za-z]+',
                                'linkedId' => '[0-9]+',
                            ],
                            'controller' => 'ApplicationProcessingNoteController',
                            'action' => 'add'
                        ]
                    ]
                ],
                'modify-note' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/notes/:action[/:id]',
                        'defaults' => [
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'controller' => 'ApplicationProcessingNoteController',
                        ]
                    ]
                ]
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
    )
);

return $routes;
