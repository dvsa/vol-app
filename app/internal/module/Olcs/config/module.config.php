<?php

return array(
    'application-name' => 'internal',
    'router' => array(
        'routes' => array(
            'dashboard' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'index',
                    )
                )
            ),
            'styleguide' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/styleguide[/:action]',
                    'defaults' => array(
                        'controller' => 'IndexController',
                    )
                )
            ),
            'operators' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/search/operators',
                    'defaults' => array(
                        'controller' => 'SearchController',
                        'action' => 'operator'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'operators-params' => array(
                        'type' => 'wildcard',
                        'options' => array(
                            'key_value_delimiter' => '/',
                            'param_delimiter' => '/',
                            'defaults' => array(
                                'page' => 1,
                                'limit' => 10
                            )
                        )
                    )
                )
            ),
            'search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/search',
                    'defaults' => array(
                        'controller' => 'SearchController',
                        'action' => 'index'
                    )
                )
            ),
            'licence_case_list' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/cases',
                    'constraints' => array(
                        'licence' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseController',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'pagination' => array(
                        'type' => 'wildcard',
                        'options' => array(
                            'key_value_delimiter' => '/',
                            'param_delimiter' => '/',
                            'defaults' => array(
                                'page' => 1,
                                'limit' => 10
                            )
                        )
                    )
                )
            ),
            'licence_case_action' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case[/:action][/:case]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseController'
                    )
                )
            ),
            'case_manage' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/action/manage/:tab',
                    'constraints' => array(
                        'case' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseController',
                        'action' => 'manage',
                        'tab' => 'summary'
                    )
                )
            ),
            'case_statement' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/statements[/:action][/:statement]',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'licence' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseStatementController',
                        'action' => 'index'
                    )
                )
            ),
            'case_appeal' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case/:case/appeals[/:action][/:appeal]',
                    'constraints' => array(
                        'case' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseAppealController',
                        'action' => 'index'
                    )
                )
            ),
            'case_convictions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/action/manage/convictions',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'statement' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseConvictionController',
                        'action' => 'index'
                    )
                )
            ),
            'conviction_ajax' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/ajax/convictions/categories',
                    'defaults' => array(
                        'controller' => 'ConvictionController',
                        'action' => 'categories',
                    )
                )
            ),
            'case_stay_action' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/stays[/:action][/:stayType][/:stay]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                        'staytype' => '[0-9]',
                        'stay' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseStayController',
                        'action' => 'index'
                    )
                )
            ),
            'case_annual_test_history' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/annual-test-history',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'CaseAnnualTestHistoryController',
                        'action' => 'index'
                    )
                )
            ),
            'case_prohibition' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/prohibitions',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'CaseProhibitionController',
                        'action' => 'index'
                    )
                )
            ),
            'conviction' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/conviction[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'CaseConvictionController',
                    )
                )
            ),
            'case_penalty' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/penalties',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'CasePenaltyController',
                        'action' => 'index'
                    )
                )
            ),
            'case_complaints' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/complaints',
                    'constraints' => array(
                        'case' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'CaseComplaintController',
                        'action' => 'index'
                    )
                )
            ),
            'complaint' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case/:case/complaints/:action[/:id]',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'licence' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseComplaintController',
                    )
                )
            ),
            'submission' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/submission[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'SubmissionController',
                    )
                )
            ),
            'note' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence[/case/:case][/:type/:typeId][/:section]/note[/:action][/:id]',
                    'defaults' => array(
                        'controller' => 'SubmissionNoteController',
                    )
                )
            ),
            'case_conditions_undertakings' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/conditions-undertakings',
                    'constraints' => array(
                        'case' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'CaseConditionUndertakingController',
                        'action' => 'index'
                    )
                )
            ),
            'conditions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case/:case/conditions/:action[/:id]',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseConditionUndertakingController',
                        'type' => 'condition'
                    )
                )
            ),

            'undertakings' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case/:case/undertaking/:action[/:id]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseConditionUndertakingController',
                        'type' => 'undertaking'
                    )
                )
            ),
            'document_generate' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/document/generate/:template[/:format][/:country]',
                    'defaults' => array(
                        'controller' => 'DocumentController',
                        'action' => 'generateDocument'
                    )
                ),
            ),
            'document_retrieve' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/document/retrieve/:filename[/:format][/:country]',
                    'defaults' => array(
                        'controller' => 'DocumentController',
                        'action' => 'retrieveDocument'
                    )
                )
            ),
            'case_impounding' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/impounding[/:action][/:id]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseImpoundingController',
                        'action' => 'index'
                    )
                )
            ),
            'case_revoke' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/:licence/case/:case/revoke/:action[/:id]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseRevokeController'
                    )
                )
            ),
            'case_pi' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/action/manage/pi[/:action][/:type][/:id]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CasePiController',
                        'action' => 'index'
                    )
                )
            ),
        ),
    ),
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController',
            'CaseController' => 'Olcs\Controller\CaseController',
            'CaseStatementController' => 'Olcs\Controller\CaseStatementController',
            'CaseAppealController' => 'Olcs\Controller\CaseAppealController',
            'CaseComplaintController' => 'Olcs\Controller\CaseComplaintController',
            'CaseConvictionController' => 'Olcs\Controller\CaseConvictionController',
            'SubmissionController' => 'Olcs\Controller\Submission\SubmissionController',
            'CaseStayController' => 'Olcs\Controller\CaseStayController',
            'CasePenaltyController' => 'Olcs\Controller\CasePenaltyController',
            'CaseProhibitionController' => 'Olcs\Controller\CaseProhibitionController',
            'CaseAnnualTestHistoryController' => 'Olcs\Controller\CaseAnnualTestHistoryController',
            'SubmissionNoteController' => 'Olcs\Controller\Submission\SubmissionNoteController',
            'CaseImpoundingController' => 'Olcs\Controller\CaseImpoundingController',
            'CaseConditionUndertakingController' => 'Olcs\Controller\CaseConditionUndertakingController',
            'CaseRevokeController' => 'Olcs\Controller\CaseRevokeController',
            'CasePiController' => 'Olcs\Controller\CasePiController',
            'DocumentController' => 'Olcs\Controller\DocumentController'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'olcs/view' => dirname(__DIR__) . '/view',
        )
    ),
    'local_forms_path' => __DIR__ . '/../src/Form/Forms/',
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
    'submission_config' => include __DIR__ . '/submission/submission.config.php',
    'local_scripts_path' => __DIR__ . '/../assets/js/inline/',
);
