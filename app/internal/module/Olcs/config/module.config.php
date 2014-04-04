<?php

return array(
    'router' => array(
        'routes' => array(
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
            ),
            'case_manage' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/case/:case/:action',
                    'constraints' => array(
                        'case' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'CaseController',
                        'action' => 'summary'
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
    ),
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
);
