<?php

return [
    'dashboard' => array(
        'type' => 'segment',
        'options' => array(
            'route' => 'dashboard[/:action]',
            'defaults' => array(
                'controller' => 'Selfserve\Dashboard\Index',
                'action' => 'index'
            )
        ),
    )
];
