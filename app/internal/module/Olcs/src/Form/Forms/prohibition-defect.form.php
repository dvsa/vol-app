<?php

return [
    'prohibition-defect' => [
        'name' => 'prohibition-defect',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'main',
                'options' => [
                    'label' => 'Prohibition defect'
                ],
                'elements' => [
                    'defectType' => [
                        'type'  => 'text',
                        'label' => 'Defect type',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax255',
                        'class' => 'extra-long'
                    ],
                    'notes' => [
                        'type'  => 'text',
                        'label' => 'Definition',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax1024',
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
