<?php

return [
    'dashboard-home' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/dashboard[/user/:userId]',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'index'
            )
        ),
    ),
    'new-licence' => array(
        'type' => 'literal',
        'options' => array(
            'route' => '/dashboard/application/create',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'createApplication'
            )
        ),
    )
];
