<?php

return [
    'prohibition' => [
        'name' => 'prohibition',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'fieldsets' => [
            [
                'name' => 'fields',
                'elements' => [
                    'prohibitionDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Prohibition date',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture',
                    ],
                    'vrm' => [
                        'type' => 'text',
                        'label' => 'Vehicle registration mark',
                    ],
                    'isTrailer' => [
                        'type' => 'checkbox-yn',
                        'label' => 'Trailer',
                    ],
                    'prohibitionType' => [
                        'type' => 'select',
                        'value_options' => 'prohibition_type',
                        'label' => 'Type',
                    ],
                    'dateCleared' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date cleared',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture',
                    ],
                    'imposedAt' => [
                        'type' => 'text',
                        'label' => 'Location prohibition issued',
                        'filters' => '\Common\Form\Elements\InputFilters\VrmOptional',
                    ],
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
            'licence' => [
                'type' => 'hidden'
            ],
            'case' => [
                'type' => 'hidden'
            ],
            'stayType' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ]
        ]
    ]
];
