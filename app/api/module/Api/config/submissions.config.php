<?php

use Dvsa\Olcs\Api\Service\Submission\Sections;

return [
    'excluded-tm-sections' => ['case-summary', 'outstanding-applications', 'people'],
    'section-types' => [
        'submission_type_o_mlh_otc' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_mlh_clo' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_clo_g' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_clo_psv' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_clo_fep' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_otc' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_env' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_irfo' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_bus_reg' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_tm' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_schedule_41' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_impounding' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
        'submission_type_o_ni_tru' => [
            Sections\CaseSummary::class,
            Sections\CaseOutline::class,
            Sections\OutstandingApplications::class,
            Sections\People::class,
        ],
    ],
    'sections' => [
        'configuration' => [
            'case-summary' => [
                'subcategoryId' => 116,
                'config' => [],
                'section_type' => ['overview'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'case-outline' => [
                'subcategoryId' => 117,
                'config' => [],
                'section_type' => ['text'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'outstanding-applications' => [
                'subcategoryId' => 119,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'most-serious-infringement'   => [
                'subcategoryId' => 118,
                'config' => [],
                'section_type' => ['overview'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'people' => [
                'subcategoryId' => 120,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'operating-centres'   => [
                'subcategoryId' => 121,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'conditions-and-undertakings'   => [
                'subcategoryId' => 122,
                'config' => [],
                'section_type' => ['list'],
                'section_editable' => false,
                'allow_comments' => true,
                'allow_attachments' => true
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
                'allow_attachments' => true
            ],
            'lead-tc-area'   => [
                'subcategoryId' => 127,
                'config' => [],
                'section_type' => ['text'],
                'allow_comments' => true,
                'allow_attachments' => true
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
                'allow_attachments' => true
            ],
            'transport-managers'   => [
                'subcategoryId' => 130,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
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
                'allow_attachments' => true
            ],
            'conviction-fpn-offence-history' => [
                'subcategoryId' => 142,
                'config' => [],
                'section_type' => ['list', 'text'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'annual-test-history'   => [
                'subcategoryId' => 143,
                'config' => [],
                'section_type' => ['text'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'penalties'   => [
                'subcategoryId' => 144,
                'config' => ['show_multiple_tables_section_header' => false],
                'section_type' => ['list', 'text'],
                'service' => 'Cases',
                'allow_comments' => true,
                'allow_attachments' => true
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
            'applicants-responses'   => [
                'subcategoryId' => 181,
                'config' => [],
                'section_type' => ['text'],
                'allow_comments' => true,
                'allow_attachments' => true,
            ],
            'applicants-comments'   => [
                'subcategoryId' => 150,
                'config' => [],
                'section_type' => ['text'],
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
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'environmental-complaints'   => [
                'subcategoryId' => 153,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'oppositions'   => [
                'subcategoryId' => 154,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
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
                'allow_attachments' => true
            ],
            'tm-details' => [
                'subcategoryId' => 160,
                'config' => [],
                'section_type' => ['overview'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'tm-qualifications' => [
                'subcategoryId' => 161,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'tm-responsibilities' => [
                'subcategoryId' => 162,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'tm-other-employment' => [
                'subcategoryId' => 163,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ],
            'tm-previous-history' => [
                'subcategoryId' => 164,
                'config' => [],
                'section_type' => ['list'],
                'allow_comments' => true,
                'allow_attachments' => true
            ]
        ]
    ]
];
