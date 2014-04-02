<?php

return [
    'business-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/business-type/:step',
            'defaults' => array(
                'controller' => 'Selfserve\BusinessType\Index',
                'action' => 'index'
            )
        ),
    ),
    
];
