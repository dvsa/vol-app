<?php

return [
    'admin_disc-printing' => [
        'name' => 'admin_disc-printing',
        'attributes' => [
            'method' => 'post',
            'class' => 'js-submit',
        ],
        'fieldsets' => [
            [
                'name' => 'operator-location',
                'options' => [
                    'label' => 'admin_disc-printing.typeOfLicence'
                ],
                'elements' => [
                    'niFlag' => [
                        'label' => 'application_type-of-licence_operator-location.data',
                        'type' => 'radio',
                        'value_options' => 'operator_locations'
                    ]
                ]
            ],
            [
                'name' => 'operator-type',
                'elements' => [
                    'goodsOrPsv' => [
                        'label' => 'application_type-of-licence_operator-type.data',
                        'type' => 'radio',
                        'value_options' => 'operator_types'
                    ]
                ]
            ],
            [
                'name' => 'licence-type',
                'elements' => [
                    'licenceType' => [
                        'type' => 'radio',
                        'label' => 'application_type-of-licence_licence-type.data',
                        'value_options' => 'licence_types',
                    ]
                ]
            ],
            [
                'name' => 'prefix',
                'options' => [
                    'label' => 'admin_disc-printing.discPrefix'
                ],
                'elements' => [
                    'discSequence' => [
                        'type' => 'select',
                    ]
                ]
            ],
            [
                'name' => 'discs-numbering',
                'options' => [
                    'label' => 'admin_disc-printing.discNumbering'
                ],
                'elements' => [
                    'startNumber' => [
                        'type' => 'text',
                        'label' => 'admin_disc-printing.startNumber',
                        'filters' => '\Common\Form\Elements\InputFilters\GoodsDiscStartNumber',
                        'required' => true
                    ],
                    'endNumber' => [
                        'type' => 'text',
                        'label' => 'admin_disc-printing.endNumber',
                        'disabled' => true
                    ],
                    'totalPages' => [
                        'type' => 'text',
                        'label' => 'admin_disc-printing.totalPages',
                        'disabled' => true
                    ],
                    'originalEndNumber' => [
                        'type' => 'hidden'
                    ],
                    'endNumberIncreased' => [
                        'type' => 'hidden'
                    ],
                ]
            ],
            [
                'name' => 'no-discs',
                'options' => [
                    'label' => 'admin_disc-printing.noDiscs'
                ],
                'attributes' => [
                    'class' => 'hidden',
                    'id' => 'noDiscs'
                ],
                'elements' => []
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
                        'label' => 'Print Discs',
                        'class' => 'action--primary large',
                    ],
                ]
            ]
        ]
    ]
];
