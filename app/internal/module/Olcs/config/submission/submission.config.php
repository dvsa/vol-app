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
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'config' => []
        ),
        'case-summary' => array(
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'application' => array(),
                    'caseType' => array(),
                    'licence' => array(
                        'children' => array(
                            'status' => array(),
                            'licenceType' => array(),
                            'goodsOrPsv' => array(),
                            'trafficArea' => array(),
                            'licenceVehicles' => array(),
                            'organisation' => array(
                                'children' => array(
                                    'type' => array(),
                                    'organisationPersons' => array(
                                        'children' => array(
                                            'person' => array(
                                                'children' => array(
                                                    'title'
                                                )
                                            )
                                        )
                                    ),
                                    'natureOfBusinesses' => array()
                                )
                            )
                        )
                    )
                )
            )
        ),
        'case-outline' => array(
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array()
        ),
        'outstanding-applications' => array(
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
                                    'licences' => array(
                                        'children' => array(
                                            'applications' => array(
                                                'criteria' => array(
                                                    'status' => array('apsts_consideration', 'apsts_granted')
                                                ),
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
                        )
                    )
                )
            )
        ),
        'most-serious-infringement'   => array(
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'seriousInfringements' => array(
                        'children' => array(
                            'memberStateCode',
                            'siCategory',
                            'siCategoryType'
                        )
                    )
                )
            )
        ),
        'persons' => array(
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
                                    'organisationPersons' => array(
                                        'children' => array(
                                            'person' => array(
                                                'children' => array(
                                                    'title'
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
        'operating-centres'   => array(
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'operatingCentres' => array(
                                'children' => array(
                                    'operatingCentre' => array(
                                        'children' => array(
                                            'address',
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'conditions-and-undertakings'   => array(
            'config' => [],
            'section_type' => ['list'],
            'section_editable' => false,
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'conditionUndertakings' => array(
                        'children' => array(
                            'case' => array(),
                            'attachedTo' => array(),
                            'conditionType' => array(),
                            'operatingCentre' => array(
                                'children' => array(
                                    'address' => array(
                                        'children' => array(
                                            'countryCode' => array()
                                        )
                                    )
                                )
                            ),
                            'addedVia' => array(),
                        )
                    )
                )
            )
        ),
        'intelligence-unit-check'   => array(
            'config' => [],
            'section_type' => [],
            'section_editable' => false,
            'allow_comments' => true,
        ),
        'interim'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'advertisement'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'linked-licences-app-numbers'   => array(
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
                                    'licences' => array(
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
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
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
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'auth-requested-applied-for'   => array(
            'config' => [],
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
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'children' => array(
                                    'licences' => array(
                                        'children' => array(
                                            'applications' => array(
                                                'children' => array(
                                                    'licence',
                                                    'transportManagers' => array(
                                                        'children' => array(
                                                            'transportManager' => array(
                                                                'children' => array(
                                                                    'tmType',
                                                                    'homeCd' => array(
                                                                        'person' => array(
                                                                            'children' => array(
                                                                                'title'
                                                                            )
                                                                        )
                                                                    ),
                                                                    'qualifications' => array(
                                                                        'children' => array(
                                                                            'qualificationType'
                                                                        )
                                                                    ),
                                                                    'otherLicences' => array(
                                                                        'children' => array(
                                                                            'application'
                                                                        )
                                                                    ),
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
                            'tmLicences' => array(
                                'children' => array(
                                    'licence',
                                    'transportManager' => array(
                                        'children' => array(
                                            'tmType',
                                            'homeCd' => array(
                                                'children' => array(
                                                    'person' => array(
                                                        'children' => array(
                                                            'title'
                                                        )
                                                    )
                                                )
                                            ),
                                            'qualifications' => array(
                                                'children' => array(
                                                    'qualificationType'
                                                )
                                            ),
                                            'otherLicences' => array(
                                                'children' => array(
                                                    'application'
                                                )
                                            ),
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ),
        'continuous-effective-control'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'fitness-and-repute'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'previous-history'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'bus-reg-app-details'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'transport-authority-comments'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'total-bus-registrations'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'local-licence-history'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'linked-mlh-history'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'registration-details'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'maintenance-tachographs-hours'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'prohibition-history' => array(
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'prohibitions' => array(
                        'children' => array(
                            'prohibitionType' => array()
                        )
                    )
                )
            )
        ),
        'conviction-fpn-offence-history' => array(
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'convictions' => array(
                        'children' => array(
                            'defendantType' => array()
                        )
                    )
                )
            )
        ),
        'annual-test-history'   => array(
            'config' => [],
            'section_type' => ['text'],
            'filter' => true,
            'allow_comments' => true,
            'service' => 'Cases',
            'bundle' => array(),
        ),
        'penalties'   => array(
            'config' => ['show_multiple_tables_section_header' => false],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'seriousInfringements' => array(
                        'children' => array(
                            'siCategory' => array(),
                            'siCategoryType' => array(),
                            'appliedPenalties' => array(
                                'children' => array(
                                    'siPenaltyType' => array(),
                                    'seriousInfringement' => array()
                                )
                            ),
                            'imposedErrus' => array(
                                'children' => array(
                                    'siPenaltyImposedType' => array()
                                )
                            ),
                            'requestedErrus' => array(
                                'children' => array(
                                    'siPenaltyRequestedType' => array()
                                )
                            ),
                            'memberStateCode' => array()
                        )
                    )
                )
            )
        ),
        'other-issues'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'te-reports'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'site-plans'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'planning-permission'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'applicants-comments'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'visibility-access-egress-size'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'compliance-complaints'   => array(
            'config' => [],
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
                            'case' => array(),
                            'complainantContactDetails' => array(
                                'children' => array(
                                    'person' => array(
                                        'children' => array(
                                            'title'
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
            'config' => [],
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
                                        'children' => array(
                                            'title'
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
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => array(
                'children' => array(
                    'oppositions' => array(
                        'children' => array(
                            'isValid',
                            'oppositionType',
                            'opposer' => array(
                                'children' => array(
                                    'contactDetails' => array(
                                        'children' => array(
                                            'person' => array(
                                                'children' => array(
                                                    'title'
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            'grounds'
                        )
                    )
                )
            )
        ),
        'financial-information'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'maps'   => array(
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
        ),
        'waive-fee-late-fee'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'surrender'   => array(
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ),
        'annex'   => array(
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
        ),
        'statements'   => array(
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'statements' => array(
                        'children' => array(
                            'statementType',
                            'requestorsContactDetails' => array(
                                'children' => array(
                                    'person' => array(
                                        'children' => array(
                                            'title'
                                        )
                                    ),
                                    'address'
                                )
                            )
                        )
                    )
                )
            )
        ),
        'tm-details' => array(
            'config' => [],
            'section_type' => ['overview'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'transportManager' => array(
                        'children' => array(
                            'tmType',
                            'homeCd' => array(
                                'children' => array(
                                    'address',
                                    'person' => array(
                                        'children' => array(
                                            'title'
                                        )
                                    )
                                )
                            ),
                            'workCd' => array(
                                'children' => array(
                                    'address'
                                )
                            )
                        )
                    )
                )
            )
        ),
        'tm-qualifications' => array(
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'transportManager' => array(
                        'children' => array(
                            'qualifications' => array(
                                'children' => array(
                                    'qualificationType',
                                    'countryCode'
                                )
                            ),

                        )
                    )
                )
            )
        ),
        'tm-responsibilities' => array(
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'transportManager' => array(
                        'children' => array(
                            'tmType',
                            'tmLicences' => array(
                                'children' => array(
                                    'licence' => array(
                                        'children' => array(
                                            'status',
                                            'organisation'
                                        )
                                    ),
                                    'operatingCentres'
                                )
                            ),
                            'tmApplications' => array(
                                'children' => array(
                                    'operatingCentres',
                                    'application' => array(
                                        'children' => array(
                                            'status',
                                            'licence' => array(
                                                'children' => array(
                                                    'organisation'
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
        'tm-other-employment' => array(
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'transportManager' => array(
                        'children' => array(
                            'employments' => array(
                                'children' => array(
                                    'contactDetails' => array(
                                        'children' => array(
                                            'address',
                                            'person' => array(
                                                'children' => array(
                                                    'title'
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
        'tm-previous-history' => array(
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => array(
                'children' => array(
                    'transportManager' => array(
                        'children' => array(
                            'otherLicences',
                            'previousConvictions',
                            'tmLicences' => array(
                                'children' => array(
                                    'licence' => array(
                                        'children' => array(
                                            'status'
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
