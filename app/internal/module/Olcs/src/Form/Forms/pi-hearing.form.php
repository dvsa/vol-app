<?php


return [
    'pi-agreed' => [
        'name' => 'Public inquiry hearing',
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
                    'venue' => [
                        'type' => 'text',
                        'label' => 'Venue',
                        'class' => 'medium'
                    ],
                    'hearingDate' => [
                        'type' => 'dateSelect',
                        'label' => 'Date'
                    ],
                    'presidingTc' => [
                        'type' => 'select',
                        'label' => 'Presiding TC',
                        'class' => 'medium'
                    ],
                    'presidedByRole' => [
                        'type' => 'select',
                        'label' => 'Presiding TC role',
                        'class' => 'medium'
                    ],
                    'piId' => [
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
                    'cancel' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    )
                ]
            ]
        ]
    ]
];
