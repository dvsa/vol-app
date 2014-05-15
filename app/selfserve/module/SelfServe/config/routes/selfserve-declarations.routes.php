<?php

return [
    'declarations' => [
        'type' => 'segment',
        'options' => [
            'route' => '/:applicationId/declarations/',
            'defaults' => [
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'index'
            ]
        ],
    ],
    'declarations-simple' => [
        'type' => 'segment',
        'options' => [
            'route' => '/:applicationId/declarations/simple',
            'defaults' => [
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'simple'
            ]
        ],
    ],
    'declarations-complete' => [
        'type' => 'segment',
        'options' => [
            'route' => '/:applicationId/declarations/complete',
            'defaults' => [
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'complete'
            ]
        ],
    ],
];

