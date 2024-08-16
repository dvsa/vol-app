<?php

return [
    'router' => [
        'routes' => [
            'document' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/scanning/document',
                    'defaults' => [
                        'controller' => Dvsa\Olcs\Scanning\Controller\DocumentController::class,
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [],
    ],
    'controllers' => [
        'factories' => [
            Dvsa\Olcs\Scanning\Controller\DocumentController::class =>
                Dvsa\Olcs\Scanning\Controller\DocumentControllerFactory::class
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => false,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/error',
        'exception_template'       => 'error/error',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'error/error'               => __DIR__ . '/../view/error/error.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ],
    ]
];
