<?php

return array(
    'router' => array(
        'routes' => array(
            'search-results-test' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/search/results',
                    'defaults' => array(
                        'controller' => 'SearchController',
                        'action' => 'results'
                    )
                )
            ),
            'olcsHome' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action'     => 'home',
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
                'type' => 'segment',
                'options' => array(
                    'route' => '/search/operators',
                    'defaults' => array(
                        'controller' => 'SearchController',
                        'action' => 'operator'
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
            'index' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'index'
                    )
                )
           ),
            'searchuser' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api/search[/:username]',
                    'constraints' => array(
                        'username' => '[a-zA-Z0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'search'
                    )
                )
            ),
            'create' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api/create',
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'create'
                    )
                )
            ),
            'update' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api/update[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'update'
                    )
                )
            ),
            'patch' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api/patch[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'patch'
                    )
                )
            ),
            'delete' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/api/delete[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'IndexController',
                        'action' => 'delete'
                    )
                )
            ),
            'case' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case[/:licence][/:action][/:case]',
                    'constraints' => array(
                        'licence' => '[0-9]+',
                        'case' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseController',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController',
            'CaseController' => 'Olcs\Controller\CaseController'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'olcs/view' => dirname(__DIR__) . '/view',
        )
    )
);
