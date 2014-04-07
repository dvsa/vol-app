<?php

return [
    'finance' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/finance[/][:step]',
            'defaults' => array(
                'controller' => 'Selfserve\Finance\Index',
                'action' => 'index'
            )
        ),
    ),
    
];
