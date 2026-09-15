<?php

return [
    'application_start' => [
        'type' => 'segment',
        'options' => [
            'route' => '/application_start_page[/]'
        ]
    ],
    'getfile' => [
        'type' => 'segment',
        'options' => [
            'route' => '/file/:identifier',
            'defaults' => [
                'controller' => 'Common\Controller\File',
                'action' => 'download'
            ]
        ]
    ],
    'transport_manager_review' => [
        'type' => 'segment',
        'options' => [
            'route' => '/transport-manager-application/review/:id[/]',
            'defaults' => [
                'controller' => Common\Controller\TransportManagerReviewController::class,
                'action' => 'index'
            ]
        ]
    ],
    'correspondence_inbox' => [
        'type' => 'segment',
        'options' => [
            'route' => '/correspondence[/]'
        ]
    ],
    'not-found' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/404[/]',
            'defaults' => [
                'controller' => \Common\Controller\ErrorController::class,
                'action' => 'notFound'
            ]
        ]
    ],
    'server-error' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/error[/]',
            'defaults' => [
                'controller' => \Common\Controller\ErrorController::class,
                'action' => 'serverError'
            ]
        ]
    ],
    'guides' => [
        'type' => 'segment',
        'options' =>  [
            'route' => '/guides[/]'
        ],
        'may_terminate' => false,
        'child_routes' => [
            'guide' => [
                'type' => 'segment',
                'options' =>  [
                    'route' => ':guide[/]',
                    'constraints' => [
                        'guide' => '[a-zA-Z\-0-9]+'
                    ],
                    'defaults' => [
                        'controller' => \Common\Controller\GuidesController::class,
                        'action' => 'index'
                    ]
                ],
            ],
        ]
    ],
];
