<?php

return [
    'prohibition-comment' => [
        'name' => 'prohibition-comment',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Prohibitions'
                ],
                'elements' => [
                    'notes' => [
                        'type'  => 'textarea',
                        'label' => 'Enter prohibitions',
                        'class' => 'extra-long'
                    ],
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'case' => [
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

