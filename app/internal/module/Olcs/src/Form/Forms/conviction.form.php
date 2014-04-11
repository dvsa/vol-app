<?php


return [
    'conviction' => [
        'name' => 'Conviction',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'defendant-details',
                'options' => [
                    'label' => 'Defendant details',
                ],
                'elements' => [
                    'defendantType' => [
                        'type' => 'select',
                        'label' => 'Defendant type:',
                        'value_options' => 'defendant_types'
                    ],
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'First name:',
                         'class' => 'long'
                    ],
                    'lastName' => [
                        'type' => 'personName',
                        'label' => 'Last name:',
                        'class' => 'long'
                    ],
                    'dateOfBirth' => [
                         'type' => 'dateSelect',
                         'label' => 'Date of birth:'
                     ],
                ]
            ],
            [
                'name' => 'offence',
                'options' => [
                    'label' => 'Offence details:',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'actsi' => [
                         'type' => 'select',
                         'label' => 'Act/si'
                    ],
                    'conviction-description' => [
                         'type' => 'select',
                         'label' => 'Conviction description:'
                    ],
                    'conviction-notes' => [
                        'type' => 'textarea',
                        'label' => 'Conviction notes:',
                        'class' => 'extra-long'
                    ],
                    'offencedate' => [
                         'type' => 'dateSelect',
                         'label' => 'Offence date:'
                     ],
                    'convictiondate' => [
                         'type' => 'dateSelect',
                         'label' => 'Conviction date:'
                     ],
                    'si' => [
                        'type' => 'select',
                        'label' => 'Si',
                        'value_options' => 'yes_no'
                    ],
                    'courtfpn' => [
                        'type' => 'text',
                        'label' => 'Court Fpn:',
                        'class' => 'medium'
                    ],
                    'penalty' => [
                        'type' => 'text',
                        'label' => 'Penalty:',
                        'class' => 'medium'
                    ],
                    'Costs' => [
                        'type' => 'text',
                        'label' => 'Costs:',
                        'class' => 'medium'
                    ],
                    'conviction-notes' => [
                        'type' => 'textarea',
                        'label' => 'Conviction notes:',
                        'class' => 'extra-long'
                    ],
                    'tic' => [
                        'type' => 'textarea',
                        'label' => 'Tic:',
                        'class' => 'extra-long'
                    ],
                    'declaredtotctr' => [
                        'type' => 'select',
                        'label' => 'Declared to TC/TR:',
                        'value_options' => 'yes_no'
                    ]
                ]
            ]
        ],
        'elements' => [
            'save-add' => [
                'type' => 'submit',
                'label' => 'Save & add another',
                'class' => 'action--primary large'
            ],
            'save' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];

