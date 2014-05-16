<?php

return [
    'dashboard' => array(
        'type' => 'segment',
        'options' => array(
            'route' => 'dashboard[/user/:userId]',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'index'
            )
        ),
    ),
    'determine-section' => array(
        'type' => 'segment',
        'options' => array(
            'route' => ':applicationId/continue-journey',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'determineSection'
            )
        ),
    ),
    'new-licence' => array(
        'type' => 'literal',
        'options' => array(
            'route' => 'dashboard/application/create',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'createApplication'
            )
        ),
    )
];
