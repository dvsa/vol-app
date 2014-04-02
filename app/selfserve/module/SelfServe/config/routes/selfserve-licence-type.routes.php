<?php

return [
    'location-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/licence-type/:step',
            'defaults' => array(
                'controller' => 'Selfserve\LicenceType\Index',
                'action' => 'index'
            )
        ),
    ),
    
];
