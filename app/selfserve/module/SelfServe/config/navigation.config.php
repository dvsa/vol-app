<?php
/*    $applicationProcess=array(
        "Tol" => Array("title" => "Type of licence", "link" => "licence-type" ),
        "Yb" => Array("title" => "Your business", "link" => "business"),
        "Ocs" => Array("title" => "Operating centres", "link" => "finance/operating-centres"),
        "Tms" => Array("title" => "Transport managers", "link" => "transport-managers"),
        "Veh" => Array("title" => "Vehicle &amp; safety", "link" => "vehicle-safety" ),
        "Ph" => Array("title" => "Previous history", "link" => "previous-history" ),
        "Ud" => Array("title" => "Declarations", "link" => "declarations" ),
        "Pay" => Array("title" => "Payment details", "link" => "payment-details"),
        "Sub" => Array("title" => "Summary", "link" => "summary" )
    );*/

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
                'label' => 'Type of licence'
            ),
            'business-type' => array(
                'dbkey' => 'Yb',
                'route' => 'business-type',
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
