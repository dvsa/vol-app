<?php

return [
    'mandatory-sections' => [
        'case-summary',
        'case-outline',
        'persons'
    ],
    'sections' => [
        'case-summary' => [
            'subcategoryId' => 116,
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'case-outline' => [
            'subcategoryId' => 117,
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'allow_attachments' => true,
            'filter' => true,
            'service' => 'Cases',
            'bundle' => []
        ],
        'outstanding-applications' => [
            'subcategoryId' => 119,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 118,
            'config' => [],
            'section_type' => ['overview'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
        'people' => [
            'subcategoryId' => 120,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 121,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 122,
            'config' => [],
            'section_type' => ['list'],
            'section_editable' => false,
            'allow_comments' => true,
            'allow_attachments' => true,
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
                    // Operating centre Cs/Us
                    'conditionUndertakings' => [
                        'criteria' => [
                            'attachedTo' => ['cat_oc']
                        ],
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
            'subcategoryId' => 123,
            'config' => [],
            'section_type' => [],
            'section_editable' => false,
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'interim'   => [
            'subcategoryId' => 124,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'advertisement'   => [
            'subcategoryId' => 125,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'linked-licences-app-numbers'   => [
            'subcategoryId' => 126,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 127,
            'config' => [],
            'section_type' => ['text'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 128,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'auth-requested-applied-for'   => [
            'subcategoryId' => 129,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 130,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 131,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'fitness-and-repute'   => [
            'subcategoryId' => 132,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'previous-history'   => [
            'subcategoryId' => 133,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'bus-reg-app-details'   => [
            'subcategoryId' => 134,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'transport-authority-comments'   => [
            'subcategoryId' => 135,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'total-bus-registrations'   => [
            'subcategoryId' => 136,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'local-licence-history'   => [
            'subcategoryId' => 137,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'linked-mlh-history'   => [
            'subcategoryId' => 138,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'registration-details'   => [
            'subcategoryId' => 139,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'maintenance-tachographs-hours'   => [
            'subcategoryId' => 140,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'prohibition-history' => [
            'subcategoryId' => 141,
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 142,
            'config' => [],
            'section_type' => ['list', 'text'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 143,
            'config' => [],
            'section_type' => ['text'],
            'filter' => true,
            'allow_comments' => true,
            'allow_attachments' => true,
            'service' => 'Cases',
            'bundle' => [],
        ],
        'penalties'   => [
            'subcategoryId' => 144,
            'config' => ['show_multiple_tables_section_header' => false],
            'section_type' => ['list', 'text'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 146,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'te-reports'   => [
            'subcategoryId' => 147,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'site-plans'   => [
            'subcategoryId' => 148,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'planning-permission'   => [
            'subcategoryId' => 149,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'applicants-comments'   => [
            'subcategoryId' => 150,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'applicants-responses'   => [
            'subcategoryId' => 181,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'visibility-access-egress-size'   => [
            'subcategoryId' => 151,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'compliance-complaints'   => [
            'subcategoryId' => 152,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 153,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
                            'operatingCentres' => [
                                'children' => [
                                    'address'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'oppositions'   => [
            'subcategoryId' => 154,
            'config' => [],
            'section_type' => ['list'],
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 155,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'maps'   => [
            'subcategoryId' => 156,
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'waive-fee-late-fee'   => [
            'subcategoryId' => 157,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'surrender'   => [
            'subcategoryId' => 158,
            'config' => [],
            'section_type' => [],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'annex'   => [
            'subcategoryId' => 159,
            'config' => [],
            'section_type' => ['file'],
            'allow_comments' => true,
            'allow_attachments' => true,
        ],
        'statements'   => [
            'subcategoryId' => 145,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 160,
            'config' => [],
            'section_type' => ['overview'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 161,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 162,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 163,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
            'subcategoryId' => 164,
            'config' => [],
            'section_type' => ['list'],
            'service' => 'Cases',
            'allow_comments' => true,
            'allow_attachments' => true,
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
