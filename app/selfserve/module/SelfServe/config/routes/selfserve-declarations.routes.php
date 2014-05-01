<?php

return [
    'declarations' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/declarations/:step',
            'defaults' => array(
                'controller' => 'Selfserve\Declarations\Index',
                'action' => 'generateStepForm'
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

