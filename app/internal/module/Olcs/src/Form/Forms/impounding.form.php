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
                        'required' => true,
                        'class' => 'medium'
                    ],
                    'applicationReceiptDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Application received',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'
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
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
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
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'class' => 'medium'
                    ],
                    'outcome' => [
                        'label' => 'Outcome',
                        'type' => 'select',
                        'value_options' => 'impounding_outcome',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'class' => 'medium'
                    ],
                    'outcomeSentDate' => [
                        'label' => 'Outcome sent date',
                        'type' => 'dateSelectWithEmpty',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture'
                    ],
                    'notes' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
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
