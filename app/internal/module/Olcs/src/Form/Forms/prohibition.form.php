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
                        'filters' => '\Common\Form\Elements\InputFilters\VrmOptional',
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
                    'clearedDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date cleared',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotRequiredNotInFuture',
                    ],
                    'imposedAt' => [
                        'type' => 'text',
                        'label' => 'Location prohibition issued',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax255',
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
            'case_id' => [
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
