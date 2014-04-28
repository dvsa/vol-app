<?php

return [
    'previous-history' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/previous-history/:step',
            'defaults' => array(
                'controller' => 'SelfServe\PreviousHistory\Index',
                'action' => 'generateStepForm'
            )
        ),
    ),
];

