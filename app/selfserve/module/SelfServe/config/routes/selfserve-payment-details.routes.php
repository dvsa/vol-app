<?php

return [
    'payment-submission' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/payment-submission/',
            'defaults' => array(
                'controller' => 'Selfserve\PaymentDetails\Index',
                'action' => 'index'
            )
        ),
    ),
    'payment-submission-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/payment-submission/complete',
            'defaults' => array(
                'controller' => 'Selfserve\PaymentDetails\Index',
                'action' => 'complete'
            )
        ),
    ),
];

