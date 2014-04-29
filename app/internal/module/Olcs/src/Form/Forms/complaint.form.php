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
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'Complainant first name:',
                         'class' => 'long'
                    ],
                    'surname' => [
                        'type' => 'personName',
                        'label' => 'Complainant last name:',
                        'class' => 'long'
                    ],
               ],
           ],
           [
                'name' => 'complaint-details',
                'options' => [
                    'label' => 'Complaint details',
                ],
                'elements' => [
                    'complaintDate' => [
                         'type' => 'dateSelect',
                         'label' => 'Complaint date:'
                     ],
                    'typeOfComplaint' => [
                        'type' => 'select',
                        'label' => 'Complaint type:',
                        'value_options' => 'complaint_types'
                    ],
                    'status' => [
                        'type' => 'select',
                        'label' => 'Complaint status:',
                        'value_options' => 'complaint_status_types'
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Description:',
                        'class' => 'extra-long'
                    ],
                    'vrm' => [
                        'type' => 'vehicleVrm',
                        'label' => 'Vehicle registration mark:',
                        'class' => 'medium'
                    ],
                ]
            ],
            [
                'name' => 'organisation-details',
                'options' => [
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'name' => [
                        'type' => 'text',
                        'label' => 'Operator:',
                        'class' => 'medium'
                    ]
                ]
            ],
            [
                'name' => 'driver-details',
                'options' => [
                    'label' => 'Driver details:',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'firstName' => [
                        'type' => 'personName',
                        'label' => 'Driver first name:',
                         'class' => 'long'
                    ],
                    'surname' => [
                        'type' => 'personName',
                        'label' => 'Driver last name:',
                        'class' => 'long'
                    ]
                ]
            ]
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
            'complaint' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-complaint',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
