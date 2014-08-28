<?php


return [
    'pi-agreed' => [
        'name' => 'Public inquiry Agreed and Legislation',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'main',
                'options' => [
                    'label' => ''
                ],
                'elements' => [
                    'piType' => [
                        'type' => 'select',
                        'label' => 'Type of PI'
                    ],
                    'caseworker' => [
                        'type' => 'select',
                        'label' => 'Caseworker assigned to',
                         'class' => 'medium'
                    ],
                    'legislation' => [
                        'type' => 'select',
                        'label' => 'Legislation',
                         'class' => 'medium'
                    ],
                    'agreedDate' => [
                        'type' => 'dateSelect',
                        'label' => 'Agreed date'
                    ],
                    'presidingTc' => [
                        'type' => 'select',
                        'label' => 'Agreed by',
                         'class' => 'medium'
                    ],
                    'role' => [
                        'type' => 'select',
                        'label' => 'Agreed by role',
                         'class' => 'medium'
                    ],
                    'comments' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Comments',
                        'class' => 'extra-long'
                    ],
                    'case' => [
                        'type' => 'hidden'
                    ],
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ]
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
                        'type' => 'reset',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large',
                        'attributes' => [
                            'type' => 'reset',
                        ]
                    ]
                ]
            ]
        ]
    ]
];
