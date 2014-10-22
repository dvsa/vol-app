<?php
return array(
    'sections' => array(
        'introduction' => array(
            'editable' => false
        ),
        'case-summary' => array(
            'editable' => false,
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
            'editable' => false,
            'bundle' => 'case-summary'
        ),
        'most-serious-infringement'   => array(
            'editable' => true,
        ),
        'persons' => array(
            'editable' => false,
            'bundle' => 'case-summary'
        ),
        'operating-centres'   => array(
            'editable' => true,
        ),
        'conditions-and-undertakings'   => array(
            'editable' => true,
        ),
        'intelligence-unit-check'   => array(
            'editable' => true,
        ),
        'interim'   => array(
            'editable' => true,
        ),
        'advertisement'   => array(
            'editable' => true,
        ),
        'linked-licences-app-numbers'   => array(
            'editable' => true,
        ),
        'lead-tc-area'   => array(
            'editable' => true,
        ),
        'current-submissions'   => array(
            'editable' => true,
        ),
        'auth-requested-applied-for'   => array(
            'editable' => true,
        ),
        'transport-managers'   => array(
            'editable' => true,
        ),
        'continuous-effective-control'   => array(
            'editable' => true,
        ),
        'fitness-repute'   => array(
            'editable' => true,
        ),
        'previous-history'   => array(
            'editable' => true,
        ),
        'bus-reg-app-details'   => array(
            'editable' => true,
        ),
        'transport-authority-comments'   => array(
            'editable' => true,
        ),
        'total-bus-registrations'   => array(
            'editable' => true,
        ),
        'local-licence-history'   => array(
            'editable' => true,
        ),
        'linked-mlh-history'   => array(
            'editable' => true,
        ),
        'registration-details'   => array(
            'editable' => true,
        ),
        'maintenance-tachographs-hours'   => array(
            'editable' => true,
        ),
        'prohibition-history'   => array(
            'editable' => true,
        ),
        'conviction-fpn-offence-history' => array(
            'editable' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => 'ALL',
                'children' => array(
                    'convictions' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'defendantType' => array(
                                'properties' => array(
                                    'id',
                                    'description'
                                )
                            ),
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
        'penalties'   => array(
            'editable' => true,
        ),
        'other-issues'   => array(
            'editable' => true,
        ),
        'te-reports'   => array(
            'editable' => true,
        ),
        'site-plans'   => array(
            'editable' => true,
        ),
        'planning-permission'   => array(
            'editable' => true,
        ),
        'applicants-comments'   => array(
            'editable' => true,
        ),
        'visibility-access-egress-size'   => array(
            'editable' => true,
        ),
        'compliance-complaints'   => array(
            'editable' => true,
        ),
        'environmental-complaints'   => array(
            'editable' => true,
        ),
        'oppositions'   => array(
            'editable' => true,
        ),
        'financial-information'   => array(
            'editable' => true,
        ),
        'maps'   => array(
            'editable' => true,
        ),
        'waive-fee-late-fee'   => array(
            'editable' => true,
        ),
        'surrender'   => array(
            'editable' => true,
        ),
        'annex'   => array(
            'editable' => true,
        )
    )
);
