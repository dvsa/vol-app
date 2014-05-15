<?php

return array(
    'journeyCompletionStatus' => array(
        0 => '',
        1 => 'incomplete',
        2 => 'complete'
    ),
    'journey' => array(
        'licence-type' => array(
            'dbkey' => 'Tol',
            'route' => 'licence-type',
            'label' => 'type-of-licence',
            'step' => 'operator-location'
        ),
        'business-type' => array(
            'dbkey' => 'Yb',
            'route' => 'business-type',
            'step' => 'details',
            'label' => 'your-business'
        ),
        'operating-centre' => array(
            'dbkey' => 'Ocs',
            'route' => 'finance/operating_centre',
            'label' => 'operating-centres'
        ),
        'transport-managers' => array(
            'dbkey' => 'Tms',
            'route' => 'transport-managers',
            'label' => 'transport-managers'
        ),
        'vehicle-safety' => array(
            'dbkey' => 'Veh',
            'route' => 'vehicle-safety/vehicle',
            'label' => 'vehicle-and-safety'
        ),
        'previous-history' => array(
            'dbkey' => 'Ph',
            'route' => 'previous-history',
            'step' => 'finance',
            'label' => 'previous-history'
        ),
        'declarations' => array(
            'dbkey' => 'Ud',
            'route' => 'declarations',
            'label' => 'declarations'
        ),
        'payment-submission' => array(
            'dbkey' => 'Pay',
            'route' => 'payment-submission',
            'label' => 'payment-submission'
        )
    )
);
