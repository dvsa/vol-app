<?php

return [
    'transport-managers' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/transport-managers/',
            'defaults' => array(
                'controller' => 'Selfserve\TransportManagers\Index',
                'action' => 'index'
            )
        ),
    ),
    'transport-managers-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/transport-managers/complete',
            'defaults' => array(
                'controller' => 'Selfserve\TransportManagers\Index',
                'action' => 'complete'
            )
        ),
    ),
];

