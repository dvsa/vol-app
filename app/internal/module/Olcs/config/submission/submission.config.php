<?php
return array(
    'sections' => array(
        'case-summary-info' => null,
        'persons' => null,
        'transport-managers' => array(
            'exclude' => array(
                'column' => 'licenceType',
                'values' => array(
                    'standard national',
                    'standard international'
                )
            )
        ),
        'outstanding-applications' => null,
        'objections' => null,
        'representations' => null,
        'complaints' => null,
        'environmental' => null,
        'previous-history' => null,
        'operating-centre' => null,
        'conditions' => null,
        'undertakings' => null,
        'annual-test-history' => null,
        'prohibition-history' => null,
        'conviction-history' => null,
        'bus-services-registered' => array(
            'exclude' => array(
                'column' => 'goodsOrPsv',
                'values' => array(
                    'psv',
                )
            )
        ),
        'bus-compliance-issues' => array(
            'exclude' => array(
                'column' => 'goodsOrPsv',
                'values' => array(
                    'psv',
                )
            )
        ),
        'current-submission' => null
    )
);
