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
                    'defType' => [
                        'type' => 'select',
                        'label' => 'Defendant type:',
                        'value_options' => 'defendant_types'
                    ],
                    'personFirstname' => [
                        'type' => 'personName',
                        'label' => 'First name:',
                         'class' => 'long'
                    ],
                    'personLastname' => [
                        'type' => 'personName',
                        'label' => 'Last name:',
                        'class' => 'long'
                    ],
                    'operatorName' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax70',
                        'label' => 'Operator name:',
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
                    /*'actsi' => [
                         'type' => 'select',
                         'label' => 'Act/si'
                    ],
                    'conviction-description' => [
                         'type' => 'select',
                         'label' => 'Conviction description:'
                    ],*/
                    'convictionNotes' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Conviction notes:',
                        'class' => 'extra-long'
                    ],
                    'dateOfOffence' => [
                         'type' => 'dateSelect',
                         'label' => 'Offence date:'
                     ],
                    'dateOfConviction' => [
                         'type' => 'dateSelect',
                         'label' => 'Conviction date:'
                     ],
                    'si' => [
                        'type' => 'select',
                        'label' => 'Si',
                        'value_options' => 'yes_no'
                    ],
                    'courtFpm' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax70',
                        'label' => 'Court Fpn:',
                        'class' => 'medium'
                    ],
                    'penalty' => [
                        'type' => 'text',
                        'label' => 'Penalty:',
                        'class' => 'medium'
                    ],
                    'costs' => [
                        'type' => 'text',
                        'label' => 'Costs:',
                        'class' => 'medium'
                    ],
                    'takenIntoConsideration' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Taken into consideration:',
                        'class' => 'extra-long'
                    ],
                    'decToTc' => [
                        'type' => 'select',
                        'label' => 'Declared to TC/TR:',
                        'value_options' => 'yes_no'
                    ]
                ]
            ]
        ],
        'elements' => [
            'vosaCase' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            /*'save-add' => [
                'type' => 'submit',
                'label' => 'Save & add another',
                'class' => 'action--primary large'
            ],*/
            'conviction' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-conviction',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];

