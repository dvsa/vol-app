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
                    'label' => 'Add recommendation'
                ],
                'elements' => [
                    'type' => [
                        'label' => 'Recommendation type',
                        'type' => 'select',
                        'value_options' => 'submission_recommendations',
                        'required' => true
                    ],
                    'sendto' => [
                        'label' => 'Send to',
                        'type' => 'select',
                        'value_options' => 'appeal_reasons',
                        'required' => true
                    ],
                    'reason' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
                        'label' => 'Reason',
                        'class' => 'extra-long'
                    ]
                ]
            ]
        ],
        'elements' => [
            'case' => [
                'type' => 'hidden'
            ],
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
            ]
        ]
    ]
];
