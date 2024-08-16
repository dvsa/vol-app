<?php

return [
    'router' => [
        'routes' => [
            'search' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/search/:index',
                    'defaults' => [
                        'controller' => 'Search'
                    ]
                ]
            ],
        ]
    ],
    'service_manager' => [
        'abstract_factories' => [
            \Laminas\Log\LoggerAbstractServiceFactory::class
        ],
        'factories' => [
            \Elasticsearch\Client::class => Dvsa\Olcs\Db\Service\Search\ClientFactory::class,
            'Elasticsearch\Search' => Dvsa\Olcs\Db\Service\Search\SearchFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [

        ],
        'factories' => [
            'Search' => Dvsa\Olcs\Db\Controller\SearchControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => false,
        'strategies' => [
            'ViewJsonStrategy'
        ],
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
             'error/404' => __DIR__ . '/../view/error/404.phtml',
             'error/index' => __DIR__ . '/../view/error/index.phtml',
             'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
         ],
    ]
];
