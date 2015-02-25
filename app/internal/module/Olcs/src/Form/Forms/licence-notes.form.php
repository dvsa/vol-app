<?php

return [
    'licence-notes' => [
        'name' => 'licence-notes',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'main',
                'elements' => [
                    'comment' => [
                        'type'  => 'text',
                        'label' => 'Enter note',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
                        'class' => 'extra-long'
                    ],
                    'priority' => [
                        'type' => 'checkbox-yn',
                        'label' => 'Priority?',
                    ],
                ]
            ],
            [
                'name' => 'form-actions',
                'attributes' => [
                    'class' => 'actions-container'
                ],
                'elements' => [
                    'submit' => [
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save',
                        'class' => 'action--primary large'
                    ],
                    'cancel' => [
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    ]
                ]
            ]
        ],
        'elements' => [
            'id' => [
                'type' => 'hidden'
            ],
            'licence' => [
                'type' => 'hidden'
            ],
            'application' => [
                'type' => 'hidden'
            ],
            'transportManager' => [
                'type' => 'hidden'
            ],
            'noteType' => [
                'type' => 'hidden'
            ],
            'linkedId' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ]
        ]
    ]
];
