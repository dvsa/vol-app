<?php


return [
    'conviction' => [
        'name' => 'Conviction',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'defendant-details',
                'options' => [
                    'label' => 'Defendant details',
                ],
                'elements' => [
                    'defType' => [
                        'type' => 'select',
                        'label' => 'Defendant type',
                        'value_options' => 'defendant_types'
                    ],
                    'personFirstname' => [
                        'type' => 'personName',
                        'label' => 'First name',
                         'class' => 'long'
                    ],
                    'personLastname' => [
                        'type' => 'personName',
                        'label' => 'Last name',
                        'class' => 'long'
                    ],
                    'operatorName' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax70',
                        'label' => 'Operator name',
                        'class' => 'long'
                    ],
                    'dateOfBirth' => [
                         'type' => 'dateSelectWithEmpty',
                         'label' => 'Date of birth',
                         'filters' => '\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture'
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
                    'parentCategory' => [
                         'type' => 'selectAjax',
                         'label' => 'Act/si',
                         'class' => 'extra-long'
                    ],
                    'category' => [
                         'type' => 'selectAjax',
                         'label' => 'Conviction description',
                         'class' => 'extra-long'
                    ],
                    'categoryText' => [
                         'type' => 'textarea',
                         'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                         'label' => 'Conviction description detail',
                        'class' => 'extra-long'
                    ],
                    'dateOfOffence' => [
                         'type' => 'dateSelectWithEmpty',
                         'label' => 'Offence date',
                         'filters' => '\Common\Form\Elements\InputFilters\OffenceDateBeforeConvictionDate'
                     ],
                    'dateOfConviction' => [
                         'type' => 'dateSelectWithEmpty',
                         'label' => 'Conviction date',
                         'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'
                     ],
                    'si' => [
                        'type' => 'select',
                        'label' => 'SI',
                        'value_options' => 'yes_no'
                    ],
                    'courtFpm' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax70',
                        'label' => 'Court/FPN',
                        'class' => 'medium'
                    ],
                    'penalty' => [
                        'type' => 'text',
                        'label' => 'Penalty',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax255RequiredNoMin',
                    ],
                    'costs' => [
                        'type' => 'text',
                        'label' => 'Costs',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax255',
                    ],
                    'convictionNotes' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Conviction notes',
                        'class' => 'extra-long'
                    ],
                    'takenIntoConsideration' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Taken into consideration',
                        'class' => 'extra-long'
                    ],
                    'decToTc' => [
                        'type' => 'select',
                        'label' => 'Declared to TC/TR',
                        'value_options' => 'yes_no'
                    ],
                    'dealtWith' => [
                        'type' => 'checkbox-yn',
                        'label' => 'Dealt with',
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
