<?php
return array(
    'sections' => array(
        'case-summary-info' => array(
            'view' => 'submission/partials/case-summary',
            'dataPath' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'submissionSections' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    ),
                    'convictions' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'category' => array(
                                'properties' => array(
                                    'id',
                                    'description'
                                )
                            )
                        )
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
                            ),
                            'transportManagerLicences' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'transportManager' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'qualifications' => array(
                                                'properties' => 'ALL'
                                            ),
                                            'contactDetails' => array(
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
                )
            )
        ),
        'persons' => array(
            'view' => 'submission/partials/persons'
        ),
        'transport-managers' => array(
            'view' => 'submission/partials/transport-managers',
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
