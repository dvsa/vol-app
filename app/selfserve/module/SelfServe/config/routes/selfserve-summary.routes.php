<?php

return [
    'summary' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/summary/',
            'defaults' => array(
                'controller' => 'Selfserve\Summary\Index',
                'action' => 'index'
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

