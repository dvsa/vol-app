<?php
return [
    'decision' => [
        'name' => 'decision',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Add decision'
                ],
                'elements' => [
                    'submissionActionStatus' => [
                        'label' => 'Decision type',
                        'type' => 'select',
                        'value_options' => 'submission_decision',
                        'required' => true
                    ],
                    'piReasons' => [
                        'type' => 'multiselect',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'label' => 'Select legislation',
                        'value_options' => 'pi-reasons',
                        'help-block' => 'Use CTRL to select multiple'
                    ],
                    'userRecipient' => [
                        'label' => 'Send to',
                        'type' => 'select',
                        'value_options' => 'user-list',
                        'required' => true
                    ],
                    'comment' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
                        'label' => 'Reason',
                        'class' => 'extra-long'
                    ],
                    'urgent' => [
                        'type' => 'checkbox-yn',
                        'label' => 'Urgent',
                    ]
                ]
            ]
        ],
        'elements' => [
            'submissionActionType' => [
                'type' => 'hidden',
                'attributes' => array(
                     'value' => 'decision'
                )
            ],
            'userSender' => [
                'type' => 'hidden',
            ],
            'submission' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-submission',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
