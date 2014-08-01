<?php

return [
    'appeal' => [
        'name' => 'appeal',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'details',
                'options' => [
                    'label' => 'Appeal Details'
                ],
                'elements' => [
                    'deadlineDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Appeal deadline',
                        'required' => false
                    ],
                    'appealDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date of appeal',
                        'filters' => '\Common\Form\Elements\InputFilters\DateRequired'
                    ],
                    'appealNumber' => [
                        'label' => 'Appeal number',
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax20'
                    ],
                    'reason' => [
                        'label' => 'Reason',
                        'type' => 'select',
                        'value_options' => 'appeal_reasons',
                    ],
                    'outlineGround' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Outline ground',
                        'class' => 'extra-long'
                    ],
                    'hearingDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date of appeal hearing',
                        'required' => false
                    ],
                    'decisionDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date of decision',
                        'required' => false
                    ],
                    'papersDue' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Papers due at tribunal',
                        'required' => false
                    ],
                    'papersSent' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Papers sent date',
                        'required' => false
                    ],
                    'outcome' => [
                        'label' => 'Outcome',
                        'type' => 'select',
                        'value_options' => 'appeal_outcomes',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty'
                    ],
                    'comment' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Comments',
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
            'case' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            /* 'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ] */
        ]
    ]
];
