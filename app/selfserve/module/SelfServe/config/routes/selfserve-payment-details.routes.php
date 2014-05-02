<?php

return [
    'payment-details' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/payment-details/',
            'defaults' => array(
                'controller' => 'Selfserve\PaymentDetails\Index',
                'action' => 'index'
            )
        ),
    ),
    'payment-details-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/payment-details/complete',
            'defaults' => array(
                'controller' => 'Selfserve\PaymentDetails\Index',
                'action' => 'complete'
            )
        ),
    ),
];

