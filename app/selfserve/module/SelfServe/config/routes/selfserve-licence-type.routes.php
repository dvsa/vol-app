<?php

return [
    'licence-type' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:licenceId/licence-type/:step',
            'defaults' => array(
                'controller' => 'Selfserve\LicenceType\Index',
                'action' => 'generateStepForm'
            )
        ),
    ),
    'licence-type-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:licenceId/licence-type/complete',
            'defaults' => array(
                'controller' => 'Selfserve\LicenceType\Index',
                'action' => 'complete'
            )
        ),
    ),
];
