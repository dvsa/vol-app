<?php

return [
    'summary' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/summary/:step',
            'defaults' => array(
                'controller' => 'Selfserve\Summary\Index',
                'action' => 'generateStepForm'
            )
        ),
    ),
    'summary-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/summary/complete',
            'defaults' => array(
                'controller' => 'Selfserve\Summary\Index',
                'action' => 'complete'
            )
        ),
    ),
];

