<?php

return [
    'transport-managers' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/transport-managers/:step',
            'defaults' => array(
                'controller' => 'Selfserve\TransportManagers\Index',
                'action' => 'generateStepForm'
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

