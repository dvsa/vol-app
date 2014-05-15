<?php

return [
    'business-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/your-business[/][:step]',
            'defaults' => array(
                'controller' => 'Selfserve\BusinessType\Index',
                'action' => 'generateStepForm',
                'step' => 'business-type',
            )
        ),
    ),
    'business-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/your-business/complete',
            'defaults' => array(
                'controller' => 'Selfserve\BusinessType\Index',
                'action' => 'complete'

            )
        ),
    ),
];
