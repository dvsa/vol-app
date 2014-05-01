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
                'label' => 'Type of licence',
                'step'  => 'operator-location'
            ),
            'business-type' => array(
                'dbkey' => 'Yb',
                'route' => 'business-type',
                'step'  => 'business-type',
                'label' => 'Your business'
            ),
            'operating-centre' => array(
                'dbkey' => 'Ocs',
                'route' => 'finance/operating_centre',
                'label' => 'Operating centres'
            ),
            'transport-managers' => array(
                'dbkey' => 'Tms',
                'route' => 'transport-managers',
                'label' => 'Transport managers'
            ),
            'vehicle-safety' => array(
                'dbkey' => 'Veh',
                'route' => 'vehicle-safety',
                'label' => 'Vehicle & safety'
            ),
            'previous-history' => array(
                'dbkey' => 'Ph',
                'route' => 'previous-history',
                'step'  => 'finance',
                'label' => 'Previous history'
            ),
            'declarations' => array(
                'dbkey' => 'Ud',
                'route' => 'declarations',
                'label' => 'Declarations'
            ),
            'payment-details' => array(
                'dbkey' => 'Pay',
                'route' => 'payment-details',
                'label' => 'Payment details'
            ),
            'summary' => array(
                'dbkey' => 'Sub',
                'route' => 'summary',
                'label' => 'Summary'
            ),
        )
    );
?>
