<?php


return [
    'complaint' => [
        'name' => 'Complaint',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'complainant-details',
                'options' => [
                    'label' => 'Complainant details',
                ],
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'Complainant first name',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\NameRequired'
                    ],
                    'surname' => [
                        'type' => 'personName',
                        'label' => 'Complainant last name',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\NameRequired'
                    ],
               ],
           ],
           [
                'name' => 'complaint-details',
                'options' => [
                    'label' => 'Complaint details',
                ],
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'complaintDate' => [
                         'type' => 'dateSelectWithEmpty',
                         'label' => 'Complaint date',
                         'filters' => '\Common\Form\Elements\InputFilters\DateNotInFuture'
                     ],
                    'typeOfComplaint' => [
                        'type' => 'select',
                        'label' => 'Complaint type',
                        'value_options' => 'complaint_types',
                        'required' => true
                    ],
                    'status' => [
                        'type' => 'select',
                        'label' => 'Complaint status',
                        'value_options' => 'complaint_status_types',
                        'required' => true
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Description',
                        'class' => 'extra-long'
                    ],
                    'vrm' => [
                        'type' => 'vehicleVrm',
                        'label' => 'Vehicle registration mark',
                        'class' => 'medium',
                        'filters' => '\Common\Form\Elements\InputFilters\VrmOptional',
                    ],
                ]
            ],
            [
                'name' => 'organisation-details',
                'options' => [
                    'label' => 'Operator details',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'name' => [
                        'type' => 'companyName',
                        'label' => 'Operator name',
                        'class' => 'medium'
                    ]
                ]
            ],
            [
                'name' => 'driver-details',
                'options' => [
                    'label' => 'Driver details',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'Driver first name',
                        'class' => 'medium',
                    ],
                    'surname' => [
                        'type' => 'personName',
                        'label' => 'Driver last name',
                        'class' => 'medium',
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
            'vosaCase' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            /* 'complaint' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-complaint',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ] */
        ]
    ]
];
