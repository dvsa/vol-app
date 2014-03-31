<?php

return [
    'business' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/business[/][:step]',
            'defaults' => array(
                'controller' => 'Selfserve\Business\Index',
                'action' => 'index'
            )
        ),
    ),
    
];
