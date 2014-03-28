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
            'search' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/search[/:action]',
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
            )
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'olcs/view' => dirname(__DIR__) . '/view',
        )
    )
);
