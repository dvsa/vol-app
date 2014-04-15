<?php

return [
    'statement' => [
        'name' => 'statement',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'details',
                'options' => [
                    'label' => 'Statement Details'
                ],
                'elements' => [
                    /*'licence_type' => [
                        'type' => 'text',
                        'label' => 'Licence type',
                        'required' => false
                    ],
                    'licence_number' => [
                        'type' => 'text',
                        'label' => 'Licence number',
                        'required' => false
                    ],*/
                    'statement_type' => [
                        'type' => 'select',
                        'label' => 'Statement type',
                        'value_options' => 'statement_types',
                        'required' => true
                    ],
                    /*'traffic_area' => [
                        'type' => 'text',
                        'label' => 'Traffic area',
                        'required' => false
                    ],*/
                    'vrm' => [
                        'type' => 'vrm'
                    ],
                    'requestors_first_name' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors first name'
                    ],
                    'requestors_family_name' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors last name'
                    ],
                    'requestors_body' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors body'
                    ],
                    'date_stopped' => [
                        'type' => 'dateSelect',
                        'label' => 'Date stopped',
                        'required' => true
                    ],
                    'date_requested' => [
                        'type' => 'dateSelect',
                        'label' => 'Date requested',
                        'required' => true
                    ],
                    'request_mode' => [
                        'type' => 'select',
                        'label' => 'Request mode',
                        'value_options' => 'request_modes',
                        'required' => true
                    ],
                    'authorised_decision' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextareaRequired',
                        'label' => 'Authorised decision',
                        'class' => 'extra-long',
                        'required' => true
                    ]
                ]
            ],
            [
                'name' => 'address',
                'options' => [
                    'label' => 'Requestors Address'
                ],
                'type' => 'address'
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
            'crsf' => [
                'type' => 'crsf',
            ],
            'saveAdd' => [
                'type' => 'submit',
                'label' => 'Save & add another',
                'class' => 'action--primary large'
            ],
            'save' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ]
        ]
    ]
];

