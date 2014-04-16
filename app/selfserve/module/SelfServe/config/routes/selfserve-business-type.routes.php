<?php

return [
    'business-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/business-type[/][:step]',
            'defaults' => array(
                'controller' => 'Selfserve\BusinessType\Index',
                'action' => 'generateStepForm'
            )
        ),
    ),
    'business-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/business-type/complete',
            'defaults' => array(
                'controller' => 'Selfserve\BusinessType\Index',
                'action' => 'complete'

            )
        ),
    ),
];
