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
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array('description')
            )
        ),
        'outstanding-applications' => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'applications' => array(
                                'children' => array(
                                    'operatingCentres',
                                    'goodsOrPsv',
                                    'publicationLinks' => array(
                                        'criteria' => array(
                                            'publicationSection' => array(1,3)
                                        ),
                                        'children' => array(
                                            'publication'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'most-serious-infringement'   => array(
            'section_type' => ['text','overview'],
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
            'section_editable' => false,
            'allow_comments' => true,
        ),
        'interim'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'advertisement'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'linked-licences-app-numbers'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array(
                    'id',
                    'licence'
                ),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id',
                            'organisation'
                        ),
                        'children' => array(
                            'organisation' => array(
                                'properties' => array(
                                    'id',
                                    'licences'
                                ),
                                'children' => array(
                                    'licences' => array(
                                        'properties' => 'ALL',
                                        'criteria' => array(
                                            'status' => array(
                                                'lsts_consideration',
                                                'lsts_granted',
                                                'lsts_curtailed',
                                                'lsts_suspended',
                                                'lsts_valid'
                                            )
                                        ),
                                        'children' => array(
                                            'status',
                                            'licenceType',
                                            'licenceVehicles'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'lead-tc-area'   => array(
            'section_type' => ['text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array(
                    'licence'
                ),
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'organisation'
                        ),
                        'children' => array(
                            'organisation' => array(
                                'properties' => array(
                                    'leadTcArea'
                                ),
                                'children' => array(
                                    'leadTcArea' => array(
                                        'name'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'current-submissions'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'auth-requested-applied-for'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'applications',
                            'licenceVehicles'
                        )
                    )
                )
            )
        ),
        'transport-managers'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'applications' => array(
                                'children' => array(
                                    'tmApplications' => array(
                                        'children' => array(
                                            'transportManager' => array(
                                                'children' => array(
                                                    'workCd' => array(
                                                        'children' => array(
                                                            'person'
                                                        )
                                                    ),
                                                    'qualifications' => array(
                                                        'children' => array(
                                                            'qualificationType'
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'tmLicences' => array(
                                'children' => array(
                                    'transportManager' => array(
                                        'children' => array(
                                            'qualifications' => array(
                                                'children' => array(
                                                    'qualificationType'
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'licenceVehicles'
                        )
                    )
                )
            )
        ),
        'continuous-effective-control'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'fitness-and-repute'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'previous-history'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'bus-reg-app-details'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'transport-authority-comments'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'total-bus-registrations'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'local-licence-history'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'linked-mlh-history'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'registration-details'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'maintenance-tachographs-hours'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'prohibition-history' => array(
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'prohibitions' => array(
                        'children' => array(
                            'prohibitionType' => array(
                                'properties' => array('id', 'description')
                            )
                        )
                    )
                )
            )
        ),
        'conviction-fpn-offence-history' => array(
            'section_type' => ['list', 'text'],
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
                            )
                        )
                    )
                )
            )
        ),
        'annual-test-history'   => array(
            'section_type' => ['text'],
            'filter' => true,
            'allow_comments' => true,
            'service' => 'Cases',
            'bundle' => array(
                'properties' => array('annualTestHistory')
            ),
        ),
        'penalties'   => array(
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'properties' => 'All',
                'children' => array(
                    'seriousInfringements' => array(
                        'children' => array(
                            'siCategory' => array(
                                'properties' => array(
                                    'description'
                                )
                            ),
                            'siCategoryType' => array(
                                'properties' => array(
                                    'description'
                                )
                            ),
                            'appliedPenalties' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'siPenaltyType' => array(
                                        'properties' => array(
                                            'id',
                                            'description'
                                        )
                                    ),
                                    'seriousInfringement' => array(
                                        'properties' => array(
                                            'id'
                                        )
                                    )
                                )
                            ),
                            'imposedErrus' => array(
                                'properties' => array(
                                    'finalDecisionDate',
                                    'startDate',
                                    'endDate',
                                    'executed'
                                ),
                                'children' => array(
                                    'siPenaltyImposedType' => array(
                                        'properties' => array(
                                            'id',
                                            'description'
                                        )
                                    )
                                )
                            ),
                            'requestedErrus' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'siPenaltyRequestedType' => array(
                                        'properties' => array(
                                            'id',
                                            'description'
                                        )
                                    )
                                )
                            ),
                            'memberStateCode' => array(
                                'properties' => array(
                                    'countryDesc'
                                )
                            )
                        )
                    )
                )
            )
        ),
        'other-issues'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'te-reports'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'site-plans'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'planning-permission'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'applicants-comments'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'visibility-access-egress-size'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'compliance-complaints'   => array(
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'complaints' => array(
                        'criteria' => array(
                            'isCompliance' => 1
                        ),
                        'children' => array(
                            'status' => [],
                            'case' => array(
                                'properties' => array(
                                    'id'
                                )
                            ),
                            'complainantContactDetails' => array(
                                'properties' => 'ALL',
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
                    )
                )
            )
        ),
        'environmental-complaints'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'complaints' => array(
                        'criteria' => array(
                            'isCompliance' => 0
                        ),
                        'children' => array(
                            'status' => array(),
                            'complainantContactDetails' => array(
                                'children' => array(
                                    'person' => array(
                                        'properties' => array(
                                            'forename',
                                            'familyName'
                                        )
                                    )
                                )
                            ),
                            'ocComplaints' => array(
                                'children' => array(
                                    'operatingCentre' => array(
                                        'children' => array(
                                            'address'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'oppositions'   => array(
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
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
        ),
        'financial-information'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'maps'   => array(
            'section_type' => ['file'],
            'allow_comments' => true,
        ),
        'waive-fee-late-fee'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'surrender'   => array(
            'section_type' => [],
            'allow_comments' => true,
        ),
        'annex'   => array(
            'section_type' => ['file'],
            'allow_comments' => true,
        ),
        'statements'   => array(
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'statements' => array(
                        'children' => array(
                            'statementType',
                            'requestorsAddress' => array(
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
                            )
                        )
                    )
                )
            )
        )
    )
);
