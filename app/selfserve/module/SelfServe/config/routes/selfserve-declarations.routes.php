<?php

return [
    'declarations' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/declarations/',
            'defaults' => array(
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'index'
            )
        ),
    ),
    'declarations-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/declarations/complete',
            'defaults' => array(
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'complete'
            )
        ),
    ),
];

