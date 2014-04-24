<?php

return [
    'search' => [
        'name' => 'search',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'search',
                'options' => [
                    0
                ],
                'elements' => [
                    'licenceNumber' => [
                        'type' => 'text',
                        'label' => 'License number',
                        'class' => 'medium',
                    ],
                    'operatorName' => [
                        'type' => 'text',
                        'label' => 'Operator/trading name',
                        'class' => 'medium',
                    ],
                    'postcode' => [
                        'type' => 'text',
                        'label' => 'Postcode',
                        'class' => 'short',
                    ],
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'First name',
                        'class' => 'long',
                    ],
                    'lastName' => [
                        'type' => 'personName',
                        'label' => 'Last name',
                        'class' => 'long',
                    ],
                    'dateOfBirth' => [
                        'type' => 'dateSelect',
                        'label' => 'Date of birth'
                    ],
                    'search' => [
                        'type' => 'submit',
                        'label' => 'Search',
                        'class' => 'action--primary large'
                    ]
                ]
            ],
            [
                'name' => 'search-advanced',
                'options' => [
                    'label' => 'Advanced search',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'address' => [
                        'type' => 'textarea',
                        'label' => 'Address',
                        'class' => 'extra-long'
                    ],
                    'town' => [
                        'type' => 'text',
                        'label' => 'Town',
                        'class' => 'long'
                    ],
                    'caseNumber' => [
                        'type' => 'text',
                        'label' => 'Case number',
                        'class' => 'medium'
                    ],
                    'transportManagerId' => [
                        'type' => 'text',
                        'label' => 'Transport manager ID',
                        'class' => 'medium'
                    ],
                    'operatorId' => [
                        'type' => 'text',
                        'label' => 'Operator ID',
                        'class' => 'medium'
                    ],
                    'vehicleRegMark' => [
                        'type' => 'text',
                        'label' => 'Vehicle registration mark',
                        'class' => 'medium'
                    ],
                    'diskSerialNumber' => [
                        'type' => 'text',
                        'label' => 'Disk serial number',
                        'class' => 'medium'
                    ],
                    'fabsRef' => [
                        'type' => 'text',
                        'label' => 'Fabs ref',
                        'class' => 'medium'
                    ],
                    'companyNo' => [
                        'type' => 'text',
                        'label' => 'Company number',
                        'class' => 'medium'
                    ]
                ]
            ]
        ],
        'elements' => [
            'advanced' => [
                'type' => 'submit',
                'label' => 'Search',
                'class' => 'action--primary large'
            ]
        ]
    ]
];
