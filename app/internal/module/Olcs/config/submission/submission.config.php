<?php
return array(
    'sections' => array(
        'introduction' => array(
        ),
        'case-summary' => array(
            'service' => 'Cases',
            'bundle' => array(
                'properties' => 'ALL',
                'children' => array(
                    'application' => array(
                        'properties' => array(
                            'targetCompletionDate'
                        )
                    ),
                    'caseType' => array(
                        'properties' => array('id')
                    ),
                    'submissionSections' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )
                    ),
                    'legacyOffences' => array(
                        'properties' => 'ALL',
                    ),
                    'licence' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'status' => array(
                                'properties' => array('id', 'description')
                            ),
                            'licenceType' => array(
                                'properties' => array('id', 'description')
                            ),
                            'goodsOrPsv' => array(
                                'properties' => array('id', 'description')
                            ),
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'licenceVehicles' => array(
                                'properties' => array(
                                    'id',
                                    'specifiedDate',
                                    'deletedDate'
                                )
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id', 'description')
                                    ),
                                    'sicCode' => array(
                                        'properties' => array('id', 'description')
                                    ),
                                    'organisationPersons' => array(
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
        'case-outline' => array(
            'bundle' => 'case-summary'
        ),
        'conviction-fpn-offence-history' => array(
            'service' => 'Cases',
            'bundle' => array(
                'properties' => 'ALL',
                'children' => array(
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
                    )
                )
            )
        ),
        'old-case-summary' => array(
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
                            'licenceType' => array(
                                'properties' => array('id')
                            ),
                            'trafficArea' => array(
                                'properties' => 'ALL'
                            ),
                            'organisation' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id')
                                    ),
                                    'sicCode' => array(
                                        'properties' => array('id')
                                    ),
                                    'organisationPersons' => array(
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
            'bundle' => 'case-summary'
        ),
        'transport-managers' => array(
            'view' => 'submission/partials/transport-managers',
            'exclude' => array(
                'column' => 'licenceType/id',
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
                'column' => 'goodsOrPsv/id',
                'values' => array(
                    'psv',
                )
            )
        ),
        'bus-compliance-issues' => array(
            'exclude' => array(
                'column' => 'goodsOrPsv/id',
                'values' => array(
                    'psv',
                )
            )
        ),
        'current-submission' => null
    )
);
