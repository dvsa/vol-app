<?php
return array(
    'sections' => array(
        'case-summary-info' => array(
            'view' => 'submission/partials/case-summary',
            'dataPath' => 'VosaCase',
            'bundle' => array(
                'children' => array(
                    'categories' => array(
                        'properties' => array(
                            'id',
                            'name'
                        )
                    ),
                    'convictions' => array(
                        'properties' => 'ALL'
                    ),
                    'licence' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'organisationOwners' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'person' => array(
                                                'properties' => 'ALL'
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'persons' => array(
            'view' => 'submission/partials/persons',
        ),
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
        'conviction-history' => array(
            'view' => 'submission/partials/conviction-history',
        ),
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
