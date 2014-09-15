<?php

return [
    'task-close' => [
        'name' => 'task-close',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'details',
                'options' => [
                    'label' => 'tasks.close.single'
                ],
                'elements' => [
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
                        'label' => 'Close',
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
            'version' => [
                'type' => 'hidden'
            ],
        ]
    ]
];
