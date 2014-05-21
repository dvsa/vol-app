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
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax75Required',
                        'label' => 'Requestors first name'
                    ],
                    'requestorsFamilyName' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax75Required',
                        'label' => 'Requestors last name'
                    ],
                    'requestorsBody' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax40Required',
                        'label' => 'Requestor body'
                    ],
                    'dateStopped' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date stopped',
                        'filters' => '\Common\Form\Elements\InputFilters\StopDateBeforeRequestDate'
                    ],
                    'dateRequested' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date requested',
                        'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'
                    ],
                    'contactType' => [
                        'type' => 'select',
                        'label' => 'Request mode',
                        'value_options' => 'contact_type',
                        'required' => true
                    ],
                    'authorisersDecision' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000Required',
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
            ],
            [
                'name' => 'document',
                'elements' => [
                    'formName' =>  [
                         'type' => 'hidden',
                         'attributes' => [
                            'value' => 'statement'
                         ]
                     ],
                    'generate' => [
                         'type' => 'checkbox-boolean',
                         'label' => 'Generate document',
                     ],
                     'templateId' => [
                         'type' => 'hidden',
                         'attributes' => [
                            'value' => 'S43_Letter'
                         ]
                     ],
                     'country' => [
                         'type' => 'hidden',
                         'attributes' => [
                            'value' => 'en_GB'
                         ]
                     ],
                     'format' => [
                         'type' => 'hidden',
                         'attributes' => [
                            'value' => 'rtf'
                         ]
                     ]
                ]
            ],
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
