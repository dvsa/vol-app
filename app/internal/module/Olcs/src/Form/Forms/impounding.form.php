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
                    ],
                    'vrm' => [
                        'type' => 'vehicleVrm',
                        'label' => 'Vehicle registration mark',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\VrmOptional',
                    ],
                    'legislationTypes' => [
                        'type' => 'multiselect',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'label' => 'Select legislation',
                        'value_options' => 'impounding_legislation',
                        'help-block' => 'Use CTRL to select multiple'
                    ],

                ]
            ],
            [
                'name' => 'hearing',
                'options' => [
                    'label' => 'Hearing',
                    'id' => 'hearing_fieldset'
                ],
                'elements' => [
                    'hearingDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Hearing date',
                        'filters' => '\Common\Form\Elements\InputFilters\HearingDateHasTime',
                    ],
                    'hearingTime' => [
                        'type' => 'timeSelect',
                        'label' => 'Hearing time (hh:mm)',
                        'filters' => '\Common\Form\Elements\InputFilters\HearingTimeHasDate',
                    ],
                    'piVenue' => [
                        'type' => 'select',
                        'label' => 'Hearing location',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'class' => 'medium'
                    ],
                    'piVenueOther' => [
                        'type' => 'text',
                        'label' => 'Other hearing location',
                        'class' => 'medium'
                    ]
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
                        'label' => 'Notes/ECMS number',
                        'class' => 'extra-long'
                    ]
                ]
            ],

            array(
                'name' => 'form-actions',
                'attributes' => array(
                    'class' => 'actions-container'
                ),
                'options' => array(0),
                'elements' => array(
                    'submit' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save',
                        'class' => 'action--primary large'
                    ),
                    'cancel' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    )
                )
            )

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
        ]
    ]
];
