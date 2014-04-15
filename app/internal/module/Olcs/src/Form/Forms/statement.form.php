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
                    'licence_type' => [
                        'type' => 'text',
                        'label' => 'Licence type',
                        'required' => false
                    ],
                    'licence_number' => [
                        'type' => 'text',
                        'label' => 'Licence number',
                        'required' => false
                    ],
                    'statement_type' => [
                        'type' => 'select',
                        'label' => 'Statement type',
                        'options' => [],
                        'required' => false
                    ],
                    'traffic_area' => [
                        'type' => 'text',
                        'label' => 'Traffic area',
                        'required' => false
                    ],
                    'vrm' => [
                        'type' => 'text',
                        'label' => 'Vehicle registration mark',
                        'required' => false
                    ],
                    'requestors_first_name' => [
                        'type' => 'text',
                        'label' => 'Requestors first name',
                        'required' => false
                    ],
                    'requestors_family_name' => [
                        'type' => 'text',
                        'label' => 'Requestors last name',
                        'required' => false
                    ],
                    'requestors_body' => [
                        'type' => 'text',
                        'label' => 'Requestors body',
                        'required' => false
                    ],
                    'date_stopped' => [
                        'type' => 'dateSelect',
                        'label' => 'Date stopped',
                        'required' => false
                    ],
                    'date_requested' => [
                        'type' => 'dateSelect',
                        'label' => 'Date requested',
                        'required' => false
                    ],
                    'request_mode' => [
                        'type' => 'select',
                        'label' => 'Request mode',
                        'options' => [],
                        'required' => false
                    ],
                    'authorised_decision' => [
                        'type' => 'textarea',
                        'label' => 'Authorised decision',
                        'class' => 'extra-long'
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
            'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ]
        ]
    ]
];

