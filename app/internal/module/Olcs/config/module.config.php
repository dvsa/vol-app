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
                        'action'     => 'index',
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
                'may_terminate' => false,
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
                    'route' => '/case/:case/action/manage/:tab',
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
                    'route' => '/case/:case/action/manage/statements[/:action][/:statement]',
                    'constraints' => array(
                        'case' => '[0-9]+',
                        'statement' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseStatementController',
                        'action' => 'index'
                    )
                )
            ),
            'fieldset' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/fieldset',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'fieldset'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController',
            'CaseController' => 'Olcs\Controller\CaseController',
            'CaseStatementController' => 'Olcs\Controller\CaseStatementController'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'olcs/view' => dirname(__DIR__) . '/view',
        )
    ),
    'local_forms_path' => __DIR__ .'/../src/Form/Forms/',
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
);
