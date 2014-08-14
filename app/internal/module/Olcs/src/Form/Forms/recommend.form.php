<?php
return [
    'recommend' => [
        'name' => 'recommend',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Add recommendation'
                ],
                'elements' => [
                    'submissionActionStatus' => [
                        'label' => 'Decision type',
                        'type' => 'select',
                        'value_options' => 'submission_recommendation',
                        'required' => true
                    ],
                    'piReasons' => [
                        'type' => 'multiselect',
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty',
                        'label' => 'Select legislation',
                        'value_options' => 'pi-reasons',
                        'help-block' => 'Use CTRL to select multiple'
                    ],
                    'recipientUser' => [
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
            'submissionActionType' => [
                'type' => 'hidden',
                'attributes' => array(
                     'value' => 'recommendation'
                )
            ],
            'senderUser' => [
                'type' => 'hidden',
            ],
            'submission' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ]
        ]
    ]
];
