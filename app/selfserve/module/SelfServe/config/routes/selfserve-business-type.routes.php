<?php

return [
    'business-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/business-type[/[:step]][/company-name/:company-name][/]',
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
