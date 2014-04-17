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
                    'statementType' => [
                        'type' => 'select',
                        'label' => 'Statement type',
                        'value_options' => 'statement_types',
                        'required' => true
                    ],
                    'vrm' => [
                        'type' => 'vrm'
                    ],
                    'requestorsForename' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors first name'
                    ],
                    'requestorsFamilyName' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors last name'
                    ],
                    'requestorsBody' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => 'Requestors body'
                    ],
                    'dateStopped' => [
                        'type' => 'dateSelect',
                        'label' => 'Date stopped',
                        'required' => true
                    ],
                    'dateRequested' => [
                        'type' => 'dateSelect',
                        'label' => 'Date requested',
                        'required' => true
                    ],
                    'contactType' => [
                        'type' => 'select',
                        'label' => 'Request mode',
                        'value_options' => 'contact_type',
                        'required' => true
                    ],
                    'authorisersDecision' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextareaRequired',
                        'label' => 'Authorised decision',
                        'class' => 'extra-long',
                        'required' => true
                    ]
                ]
            ],
            [
                'name' => 'requestorsAddress',
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
