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
                        'label' => 'Lic #',
                        'class' => 'medium',
                        'placeholder' => 'Licence number'
                    ],
                    'operatorName' => [
                        'type' => 'text',
                        'label' => 'Operator / trading name',
                        'class' => 'medium',
                        'placeholder' => 'Trading name'
                    ],
                    'postcode' => [
                        'type' => 'text',
                        'label' => 'Postcode',
                        'class' => 'short',
                        'placeholder' => 'Postcode'
                    ],
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'First name',
                        'class' => 'long',
                        'placeholder' => 'First name'
                    ],
                    'lastName' => [
                        'type' => 'personName',
                        'label' => 'Last name',
                        'class' => 'long',
                        'placeholder' => 'Last name'
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
                'name' => 'advanced',
                'options' => [
                    'label' => 'Advanced Search',
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
                        'label' => 'Case Number',
                        'class' => 'medium'
                    ],
                    'transportManagerId' => [
                        'type' => 'text',
                        'label' => 'Transport Manager ID',
                        'class' => 'medium'
                    ],
                    'operatorId' => [
                        'type' => 'text',
                        'label' => 'Operator ID',
                        'class' => 'medium'
                    ],
                    'vehicleRegMark' => [
                        'type' => 'text',
                        'label' => 'Vehicle Registration Mark',
                        'class' => 'medium'
                    ],
                    'diskSerialNumber' => [
                        'type' => 'text',
                        'label' => 'Disk Serial Number',
                        'class' => 'medium'
                    ],
                    'fabsRef' => [
                        'type' => 'text',
                        'label' => 'Fabs Ref',
                        'class' => 'medium'
                    ],
                    'companyNo' => [
                        'type' => 'text',
                        'label' => 'Company No',
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
