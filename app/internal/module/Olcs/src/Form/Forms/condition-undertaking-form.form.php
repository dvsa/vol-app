<?php


return [
    'condition-undertaking-form' => [
        'name' => 'Complaint',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'condition-undertaking',
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'vosaCase' => [
                        'type' => 'hidden'
                    ],
                    'licence' => [
                        'type' => 'hidden'
                    ],
                    'conditionType' => [
                        'type' => 'hidden'
                    ],
                    'addedVia' => [
                        'type' => 'hidden',
                        'value' => 'Case'
                    ],
                    'isDraft' => [
                        'type' => 'hidden',
                        'value' => 0,
                    ],
                    'notes' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
                        'class' => 'extra-long',

                    ],
                    'isFulfilled' => [
                        'type' => 'checkbox-boolean',
                        'label' => 'Fulfilled',
                    ],
                    'attachedTo' => [
                        'type' => 'select-group',
                        'label' => 'Attached to'
                    ],
               ],
           ],
        ],
        'elements' => [
            'condition-undertaking-submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-conditionUndertaking',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
