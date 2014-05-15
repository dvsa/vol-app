<?php
return [
    'note' => [
        'name' => 'note',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => ['main' =>
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Add note'
                ],
                'elements' => [
                    'note' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Note',
                        'class' => 'extra-long'
                    ],
                ]
            ]
        ],
        'elements' => [
            'id' => [
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
                'name' => 'cancel-note',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
