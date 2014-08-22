<?php

return [
    'application_start' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/dashboard[/:action]',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'index'
            )
        ),
    )
];
