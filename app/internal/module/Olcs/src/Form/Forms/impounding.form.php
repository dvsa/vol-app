<?php

return [
    'impounding' => [
        'name' => 'impounding',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'application_details',
                'options' => [
                    'label' => 'Application details'
                ],
                'elements' => [
                    'impoundingType' => [
                        'type' => 'select',
                        'label' => 'Impounding type',
                        'value_options' => 'impounding_type',
                        'required' => false,
                        'class' => 'medium'
                    ],
                    'applicationReceiptDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Application received',
                        'required' => false
                    ]
                ]
            ],
            [
                'name' => 'hearing',
                'id' => 'hearing',
                'options' => [
                    'label' => 'Hearing'
                ],
                'elements' => [
                    'hearingDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Hearing date',
                        'required' => false
                    ],
                    'hearingTime' => [
                        'type' => 'timeSelect',
                        'label' => 'Hearing time',
                        'required' => false
                    ],
                    'hearingLocation' => [
                        'type' => 'select',
                        'label' => 'Hearing location',
                        'value_options' => 'hearing_location',
                        'required' => false,
                        'class' => 'medium'
                    ],
                ],
            ],
            [
                'name' => 'outcome',
                'options' => [
                    'label' => 'Outcome'
                ],
                'elements' => [
                    'presidingTc' => [
                        'type' => 'select',
                        'label' => 'Presiding TC/DTC',
                        'value_options' => 'presiding_tc',
                        'required' => false,
                        'class' => 'medium'
                    ],
                    'outcome' => [
                        'label' => 'Outcome',
                        'type' => 'select',
                        'value_options' => 'impounding_outcome',
                        'required' => true,
                        'class' => 'medium'
                    ],
                    'outcomeSentDate' => [
                        'label' => 'Outcome sent date',
                        'type' => 'dateSelectWithEmpty',
                        'required' => true
                    ],
                    'notes' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
                        'label' => 'Notes',
                        'class' => 'extra-long'
                    ]
                ]
            ],

        ],
        'elements' => [
            'case' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            'crsf' => [
                'type' => 'crsf',
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
