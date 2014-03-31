<?php

return [
    'licence-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/licence-type/:step',
            'defaults' => array(
                'controller' => 'Selfserve\LicenceType\Index',
                'action' => 'index'
            )
        ),
    ),
    'licence-type-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/licence-type/complete',
            'defaults' => array(
                'controller' => 'Selfserve\LicenceType\Index',
                'action' => 'complete'
            )
        ),
    ),
];
