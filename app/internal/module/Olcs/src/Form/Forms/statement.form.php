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
                        'type' => 'personName',
                        'label' => 'Requestors first name',
                        'filters' => '\Common\Form\Elements\InputFilters\NameRequired'
                    ],
                    'requestorsFamilyName' => [
                        'type' => 'personName',
                        'label' => 'Requestors last name',
                        'filters' => '\Common\Form\Elements\InputFilters\NameRequired'
                    ],
                    'requestorsBody' => [
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax40Required',
                        'label' => 'Requestor body'
                    ],
                    'stoppedDate' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => 'Date stopped',
                        'filters' => '\Common\Form\Elements\InputFilters\StopDateBeforeRequestDate'
                    ],
                    'requestedDate' => [
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
                     'countryCode' => [
                         'type' => 'hidden',
                         'attributes' => [
                            'value' => 'GB'
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
            'case' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            /* 'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ] */
        ]
    ]
];
