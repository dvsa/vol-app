<?php

return array(
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
                        'case' => '[0-9]+'
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
            'case_stay_action' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/action/manage/stays[/:action][/:stayType][/:stay]',
                    'constraints' => array(
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
            'conviction' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/[:case]/conviction[/:action][/:id]',
                    'defaults' => array(
                        'controller' => 'ConvictionController',
                    )
                )
            ),
            'case_complaints' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/licence/[:licence]/case/:case/complaints[/:action][/:complaint]',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'complaint' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseComplaintController',
                        'action' => 'index'
                    )
                )
            ),
        )
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
            'ConvictionController' => 'Olcs\Controller\ConvictionController',
            'CaseConvictionController' => 'Olcs\Controller\CaseConvictionController',
            'CaseStatementController' => 'Olcs\Controller\CaseStatementController',
            'CaseStatementController' => 'Olcs\Controller\CaseStatementController',
            'CaseAppealController' => 'Olcs\Controller\CaseAppealController',
            'CaseStayController' => 'Olcs\Controller\CaseStayController',
            'CaseComplaintController' => 'Olcs\Controller\CaseComplaintController'
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
);
