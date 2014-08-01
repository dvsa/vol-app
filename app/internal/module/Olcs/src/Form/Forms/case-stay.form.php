<?php

return [
    'case-stay' => [
        'name' => 'case-stay',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'fields',
                'elements' => [
                    'requestDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date of request',
                        'class' => 'extra-long',
                        'filters' => '\Common\Form\Elements\InputFilters\DateRequired',
                    ],
                    'outcome' => [
                        'type' => 'select',
                        'label' => 'Outcome',
                        'value_options' => 'case_stay_outcome',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty'
                    ],
                    'notes' => [
                        'type' => 'textarea',
                        'label' => 'Notes',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'class' => 'extra-long'
                    ],
                    'isWithdrawn' => [
                        'type' => 'checkbox-yn',
                        'label' => 'Is withdrawn?',
                    ],
                    'withdrawnDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Withdrawn date',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture'
                    ],
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
            'licence' => [
                'type' => 'hidden'
            ],
            'case' => [
                'type' => 'hidden'
            ],
            'stayType' => [
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
