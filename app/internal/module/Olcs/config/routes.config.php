<?php
return [
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
    'styleguide' => [
        'type' => 'segment',
        'options' => [
            'route' => '/styleguide[/:action]',
            'defaults' => [
                'controller' => 'IndexController',
            ]
        ]
    ],
    'operators' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/search/operators',
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
            'route' => '/search',
            'defaults' => [
                'controller' => 'SearchController',
                'action' => 'index'
            ]
        ]
    ],
    'licence_case_list' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/cases',
            'constraints' => [
                'licence' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController',
                'action' => 'index'
            ]
        ],
        'may_terminate' => true,
        'child_routes' => [
            'pagination' => [
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
    'licence_case_action' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case[/:action][/:case]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController'
            ]
        ]
    ],
    'case_manage' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/:case/action/manage/:tab',
            'constraints' => [
                'case' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseController',
                'action' => 'manage',
                'tab' => 'overview'
            ]
        ]
    ],
    'case_statement' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/statements[/:action][/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'licence' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseStatementController',
                'action' => 'index'
            ]
        ]
    ],
    'case_appeal' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/appeals[/:action][/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseAppealController',
                'action' => 'index'
            ]
        ]
    ],
    'case_convictions' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/:case/action/manage/convictions',
            'constraints' => [
                'case' => '[0-9]+',
                'statement' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseConvictionController',
                'action' => 'index'
            ]
        ]
    ],
    'conviction_ajax' => [
        'type' => 'Literal',
        'options' => [
            'route' => '/ajax/convictions/categories',
            'defaults' => [
                'controller' => 'ConvictionController',
                'action' => 'categories',
            ]
        ]
    ],
    'case_stay_action' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/action/manage/stays[/:action][/:stayType][/:stay]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
                'staytype' => '[0-9]',
                'stay' => '[0-9]+'
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
            'route' => '/licence/[:licence]/case/[:case]/action/manage/annual-test-history',
            'constraints' => [
                'licence' => '[0-9]+',
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
            'route' => '/licence/[:licence]/case/[:case]/action/manage/prohibitions',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseProhibitionController',
                'action' => 'index'
            ]
        ]
    ],
    'conviction' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/conviction[/:action][/][:id]',
            'defaults' => [
                'controller' => 'CaseConvictionController',
            ]
        ]
    ],
    'case_penalty' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/action/manage/penalties',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CasePenaltyController',
                'action' => 'index'
            ]
        ]
    ],
    'case_complaints' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/:case/complaints',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseComplaintController',
                'action' => 'index'
            ]
        ]
    ],
    'complaint' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/complaints/:action[/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'licence' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseComplaintController',
            ]
        ]
    ],
    'submission' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/submission[/:action][/][:id]',
            'defaults' => [
                'controller' => 'SubmissionController',
            ]
        ]
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
            'route' => '/licence/[:licence]/case/:case/conditions-undertakings',
            'constraints' => [
                'case' => '[0-9]+',
            ],
            'defaults' => [
                'controller' => 'CaseConditionUndertakingController',
                'action' => 'index'
            ]
        ]
    ],
    'conditions' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/conditions/:action[/:id]',
            'constraints' => [
                'case' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseConditionUndertakingController',
                'type' => 'condition'
            ]
        ]
    ],
    'undertakings' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/:licence/case/:case/undertaking/:action[/:id]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CaseConditionUndertakingController',
                'type' => 'undertaking'
            ]
        ]
    ],
    'document_generate' => [
        'type' => 'segment',
        'options' => [
            'route' => '/document/generate/:template[/:format][/:country]',
            'defaults' => [
                'controller' => 'DocumentController',
                'action' => 'generateDocument'
            ]
        ],
    ],
    'document_retrieve' => [
        'type' => 'segment',
        'options' => [
            'route' => '/document/retrieve/:filename[/:format][/:country]',
            'defaults' => [
                'controller' => 'DocumentController',
                'action' => 'retrieveDocument'
            ]
        ]
    ],
    'case_impounding' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/action/manage/impounding[/:action][/:id]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
                'id' => '[0-9]+'
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
            'route' => '/licence/:licence/case/:case/revoke/:action[/:id]',
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
    'case_pi' => [
        'type' => 'segment',
        'options' => [
            'route' => '/licence/[:licence]/case/[:case]/action/manage/pi[/:action][/:type][/:id]',
            'constraints' => [
                'licence' => '[0-9]+',
                'case' => '[0-9]+',
                'id' => '[0-9]+'
            ],
            'defaults' => [
                'controller' => 'CasePiController',
                'action' => 'index'
            ]
        ]
    ],
];