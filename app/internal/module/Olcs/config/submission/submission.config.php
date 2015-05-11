<?php
return [
    'mandatory-sections' => [
        'introduction',
        'case-summary',
        'case-outline',
        'persons'
    ],
    'sections' => [
        'introduction' => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'config' => []
        ],
        'case-summary' => [
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'application' => [],
                    'caseType' => [],
                    'licence' => [
                        'children' => [
                            'status' => [],
                            'licenceType' => [],
                            'goodsOrPsv' => [],
                            'trafficArea' => [],
                            'licenceVehicles' => [],
                            'organisation' => [
                                'children' => [
                                    'type' => [],
                                    'organisationPersons' => [
                                        'children' => [
                                            'person' => [
                                                'children' => [
                                                    'title'
                                                ]
                                            ]
                                        ]
                                    ],
                                    'natureOfBusinesses' => []
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'case-outline' => [
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => []
        ],
        'outstanding-applications' => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'children' => [
                                    'licences' => [
                                        'children' => [
                                            'applications' => [
                                                'criteria' => [
                                                    'status' => ['apsts_consideration', 'apsts_granted']
                                                ],
                                                'children' => [
                                                    'operatingCentres',
                                                    'goodsOrPsv',
                                                    'publicationLinks' => [
                                                        'criteria' => [
                                                            'publicationSection' => [1,3]
                                                        ],
                                                        'children' => [
                                                            'publication'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'most-serious-infringement'   => [
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'seriousInfringements' => [
                        'children' => [
                            'memberStateCode',
                            'siCategory',
                            'siCategoryType'
                        ]
                    ]
                ]
            ]
        ],
        'persons' => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'children' => [
                                    'organisationPersons' => [
                                        'children' => [
                                            'person' => [
                                                'children' => [
                                                    'title'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'operating-centres'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'operatingCentres' => [
                                'children' => [
                                    'operatingCentre' => [
                                        'children' => [
                                            'address',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'conditions-and-undertakings'   => [
            'config' => [],
            'section_type' => ['list'],
            'section_editable' => false,
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [ // Licence Cs/Us
                        'children' => [
                            'conditionUndertakings' => [
                                'children' => [
                                    'case',
                                    'attachedTo',
                                    'conditionType',
                                    'operatingCentre' => [
                                        'children' => [
                                            'address' => [
                                                'children' => [
                                                    'countryCode'
                                                ]
                                            ]
                                        ]
                                    ],
                                    'addedVia' => [],
                                ]
                            ],
                            'applications' => [
                                'children' => [
                                    'conditionUndertakings' => [
                                        'children' => [
                                            'case',
                                            'attachedTo',
                                            'conditionType',
                                            'operatingCentre' => [
                                                'children' => [
                                                    'address' => [
                                                        'children' => [
                                                            'countryCode'
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            'addedVia' => [],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    // Case Cs/Us
                    'conditionUndertakings' => [
                        'children' => [
                            'case',
                            'attachedTo',
                            'conditionType',
                            'operatingCentre' => [
                                'children' => [
                                    'address' => [
                                        'children' => [
                                            'countryCode'
                                        ]
                                    ]
                                ]
                            ],
                            'addedVia' => [],
                        ]
                    ]
                ]
            ]
        ],
        'intelligence-unit-check'   => [
            'config' => [],
            'section_type' => [],
            'section_editable' => false,
            'allow_comments' => true,
        ],
        'interim'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'advertisement'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'linked-licences-app-numbers'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'children' => [
                                    'licences' => [
                                        'criteria' => [
                                            'status' => [
                                                'lsts_consideration',
                                                'lsts_granted',
                                                'lsts_curtailed',
                                                'lsts_suspended',
                                                'lsts_valid'
                                            ]
                                        ],
                                        'children' => [
                                            'status',
                                            'licenceType',
                                            'licenceVehicles'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'lead-tc-area'   => [
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'children' => [
                                    'leadTcArea' => [
                                        'name'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'current-submissions'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'auth-requested-applied-for'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'applications',
                            'licenceVehicles'
                        ]
                    ]
                ]
            ]
        ],
        'transport-managers'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'licence' => [
                        'children' => [
                            'organisation' => [
                                'children' => [
                                    'licences' => [
                                        'children' => [
                                            'applications' => [
                                                'children' => [
                                                    'licence',
                                                    'transportManagers' => [
                                                        'children' => [
                                                            'transportManager' => [
                                                                'children' => [
                                                                    'tmType',
                                                                    'homeCd' => [
                                                                        'person' => [
                                                                            'children' => [
                                                                                'title'
                                                                            ]
                                                                        ]
                                                                    ],
                                                                    'qualifications' => [
                                                                        'children' => [
                                                                            'qualificationType'
                                                                        ]
                                                                    ],
                                                                    'otherLicences' => [
                                                                        'children' => [
                                                                            'application'
                                                                        ]
                                                                    ],
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'tmLicences' => [
                                'children' => [
                                    'licence',
                                    'transportManager' => [
                                        'children' => [
                                            'tmType',
                                            'homeCd' => [
                                                'children' => [
                                                    'person' => [
                                                        'children' => [
                                                            'title'
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            'qualifications' => [
                                                'children' => [
                                                    'qualificationType'
                                                ]
                                            ],
                                            'otherLicences' => [
                                                'children' => [
                                                    'application'
                                                ]
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'continuous-effective-control'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'fitness-and-repute'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'previous-history'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'bus-reg-app-details'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'transport-authority-comments'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'total-bus-registrations'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'local-licence-history'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'linked-mlh-history'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'registration-details'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'maintenance-tachographs-hours'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'prohibition-history' => [
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'prohibitions' => [
                        'children' => [
                            'prohibitionType' => []
                        ]
                    ]
                ]
            ]
        ],
        'conviction-fpn-offence-history' => [
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'convictions' => [
                        'children' => [
                            'defendantType' => []
                        ]
                    ]
                ]
            ]
        ],
        'annual-test-history'   => [
            'config' => [],
            'section_type' => ['text'],
            'filter' => true,
            'allow_comments' => true,
            'service' => 'Cases',
            'bundle' => [],
        ],
        'penalties'   => [
            'config' => ['show_multiple_tables_section_header' => false],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'seriousInfringements' => [
                        'children' => [
                            'siCategory' => [],
                            'siCategoryType' => [],
                            'appliedPenalties' => [
                                'children' => [
                                    'siPenaltyType' => [],
                                    'seriousInfringement' => []
                                ]
                            ],
                            'imposedErrus' => [
                                'children' => [
                                    'siPenaltyImposedType' => []
                                ]
                            ],
                            'requestedErrus' => [
                                'children' => [
                                    'siPenaltyRequestedType' => []
                                ]
                            ],
                            'memberStateCode' => []
                        ]
                    ]
                ]
            ]
        ],
        'other-issues'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'te-reports'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'site-plans'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'planning-permission'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'applicants-comments'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'visibility-access-egress-size'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'compliance-complaints'   => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'complaints' => [
                        'criteria' => [
                            'isCompliance' => 1
                        ],
                        'children' => [
                            'status' => [],
                            'case' => [],
                            'complainantContactDetails' => [
                                'children' => [
                                    'person' => [
                                        'children' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'environmental-complaints'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'complaints' => [
                        'criteria' => [
                            'isCompliance' => 0
                        ],
                        'children' => [
                            'status' => [],
                            'complainantContactDetails' => [
                                'children' => [
                                    'person' => [
                                        'children' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ],
                            'ocComplaints' => [
                                'children' => [
                                    'operatingCentre' => [
                                        'children' => [
                                            'address'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'oppositions'   => [
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => [
                'children' => [
                    'oppositions' => [
                        'children' => [
                            'isValid',
                            'oppositionType',
                            'opposer' => [
                                'children' => [
                                    'contactDetails' => [
                                        'children' => [
                                            'person' => [
                                                'children' => [
                                                    'title'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'grounds'
                        ]
                    ]
                ]
            ]
        ],
        'financial-information'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'maps'   => [
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
        ],
        'waive-fee-late-fee'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'surrender'   => [
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
        ],
        'annex'   => [
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
        ],
        'statements'   => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'statements' => [
                        'children' => [
                            'statementType',
                            'requestorsContactDetails' => [
                                'children' => [
                                    'person' => [
                                        'children' => [
                                            'title'
                                        ]
                                    ],
                                    'address'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'tm-details' => [
            'config' => [],
            'section_type' => ['overview'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'transportManager' => [
                        'children' => [
                            'tmType',
                            'homeCd' => [
                                'children' => [
                                    'address',
                                    'person' => [
                                        'children' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ],
                            'workCd' => [
                                'children' => [
                                    'address'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'tm-qualifications' => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'transportManager' => [
                        'children' => [
                            'qualifications' => [
                                'children' => [
                                    'qualificationType',
                                    'countryCode'
                                ]
                            ],

                        ]
                    ]
                ]
            ]
        ],
        'tm-responsibilities' => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'transportManager' => [
                        'children' => [
                            'tmType',
                            'tmLicences' => [
                                'children' => [
                                    'licence' => [
                                        'children' => [
                                            'status',
                                            'organisation'
                                        ]
                                    ],
                                    'operatingCentres'
                                ]
                            ],
                            'tmApplications' => [
                                'children' => [
                                    'operatingCentres',
                                    'application' => [
                                        'children' => [
                                            'status',
                                            'licence' => [
                                                'children' => [
                                                    'organisation'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'tm-other-employment' => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'transportManager' => [
                        'children' => [
                            'employments' => [
                                'children' => [
                                    'contactDetails' => [
                                        'children' => [
                                            'address',
                                            'person' => [
                                                'children' => [
                                                    'title'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'tm-previous-history' => [
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'filter' => true,
            'bundle' => [
                'children' => [
                    'transportManager' => [
                        'children' => [
                            'otherLicences',
                            'previousConvictions',
                            'tmLicences' => [
                                'children' => [
                                    'licence' => [
                                        'children' => [
                                            'status'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
