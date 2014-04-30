<?php
return [
    'recommend' => [
        'name' => 'recommend',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => ['main' =>
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Add decision'
                ],
                'elements' => [
                    'submissionActionStatus' => [
                        'label' => 'Decision type',
                        'type' => 'select',
                        'value_options' => 'submission_recommendation',
                        'required' => true
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
                     'value' => 'recommendation'
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
