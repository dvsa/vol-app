<?php
return array(
    'mandatory-sections' => array(
        'introduction',
        'case-summary',
        'case-outline',
        'persons'
    ),
    'sections' => array(
        'introduction' => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'case-summary' => array(
            'section_type' => ['overview'],
            'data_field' => '',
            'allow_comments' => true,
            'filter' => true,
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
                                    'organisationPersons' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'person' => array(
                                                'properties' => 'ALL'
                                            )
                                        )
                                    ),
                                    'natureOfBusinesss' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'refData' => array(
                                                'properties' => array(
                                                    'id',
                                                    'description'
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
        'case-outline' => array(
            'section_type' => ['text'],
            'data_field' => 'outline',
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array('description')
            )
        ),
        'most-serious-infringement'   => array(
            'section_type' => ['text','overview'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'persons' => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => 'ALL',
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
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
        'operating-centres'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
        ),
        'conditions-and-undertakings'   => array(
            'section_type' => ['list'],
            'section_editable' => false,
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array('id'),
                'children' => array(
                    'conditionUndertakings' => array(
                        'properties' => 'ALL',
                        'children' => array(
                            'case' => array(
                                'properties' => array('id')
                            ),
                            'attachedTo' => array(
                                'properties' => array('id', 'description')
                            ),
                            'conditionType' => array(
                                'properties' => array('id', 'description')
                            ),
                            'operatingCentre' => array(
                                'properties' => array('id'),
                                'children' => array(
                                    'address' => array(
                                        'properties' => array(

                                            'addressLine1',
                                            'addressLine2',
                                            'addressLine3',
                                            'addressLine4',
                                            'town',
                                            'postcode'
                                        ),
                                        'children' => array(
                                            'countryCode' => array(
                                                'properties' => array(
                                                    'id'
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'addedVia' => array(
                                'properties' => array('id', 'description')
                            ),
                        )
                    )
                )
            )
        ),
        'intelligence-unit-check'   => array(
            'section_type' => [],
            'data_field' => '',
            'section_editable' => false,
            'allow_comments' => true,
        ),
        'interim'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'advertisement'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'linked-licences-app-numbers'   => array(
            'section_type' => ['list'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'lead-tc-area'   => array(
            'section_type' => ['text'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'current-submissions'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'auth-requested-applied-for'   => array(
            'section_type' => ['text'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'transport-managers'   => array(
            'section_type' => ['list'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'continuous-effective-control'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'fitness-and-repute'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'previous-history'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'bus-reg-app-details'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'transport-authority-comments'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'total-bus-registrations'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'local-licence-history'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'linked-mlh-history'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'registration-details'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'maintenance-tachographs-hours'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'prohibition-history'   => array(
            'section_type' => ['list', 'text'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'conviction-fpn-offence-history' => array(
            'section_type' => ['list', 'text'],
            'data_field' => '',
            'allow_comments' => true,
            'filter' => true,
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
        'annual-test-history'   => array(
            'section_type' => ['text'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'penalties'   => array(
            'section_type' => ['list', 'text'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'other-issues'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'te-reports'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'site-plans'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'planning-permission'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'applicants-comments'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'visibility-access-egress-size'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'compliance-complaints'   => array(
            'section_type' => ['list'],
            'data_field' => '',
            'service' => 'Complaint',
            'identifier' => 'case',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'properties' => array(
                    'id',
                    'complainantForename',
                    'complainantFamilyName',
                    'complaintDate',
                    'description',
                    'case'
                ),
                'children' => array(
                    'case' => array(
                        'properties' => array(
                            'id'
                        )
                    )
                )
            )
        ),
        'environmental-complaints'   => array(
            'section_type' => ['list'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'oppositions'   => array(
            'section_type' => ['list'],
            'data_field' => '',
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'properties' => array(
                        'id'
                    ),
                    'application' => array(
                        'properties' => array(
                            'id'
                        ),
                        'children' => array(
                            'oppositions' => array(
                                'children' => array(
                                    'oppositionType' => array(
                                        'properties' => array(
                                            'description'
                                        )
                                    ),
                                    'opposer' => array(
                                        'children' => array(
                                            'contactDetails' => array(
                                                'children' => array(
                                                    'person' => array(
                                                        'properties' => array(
                                                            'forename',
                                                            'familyName'
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                    'grounds' => array(
                                        'children' => array(
                                            'grounds' => array(
                                                'properties' => array(
                                                    'id',
                                                    'description'
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
        'financial-information'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'maps'   => array(
            'section_type' => ['file'],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'waive-fee-late-fee'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'surrender'   => array(
            'section_type' => [],
            'data_field' => '',
            'allow_comments' => true,
        ),
        'annex'   => array(
            'section_type' => ['file'],
            'data_field' => '',
            'allow_comments' => true,
        )
    )
);
