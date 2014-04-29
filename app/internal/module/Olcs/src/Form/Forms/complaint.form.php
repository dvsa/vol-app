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
                    'personFirstname' => [
                        'type' => 'personName',
                        'label' => 'Complainant first name:',
                         'class' => 'long'
                    ],
                    'personLastname' => [
                        'type' => 'personName',
                        'label' => 'Complainant last name:',
                        'class' => 'long'
                    ],
                    'complaintDate' => [
                         'type' => 'dateSelect',
                         'label' => 'Complaint date:'
                     ],
                    'complaintType' => [
                        'type' => 'select',
                        'label' => 'Complaint type:',
                        'value_options' => 'complaint_types'
                    ],
                    'complaintStatus' => [
                        'type' => 'select',
                        'label' => 'Complaint status:',
                        'value_options' => 'complaint_status_types'
                    ],
                ]
            ],
            [
                'name' => 'driver-details',
                'options' => [
                    'label' => 'Driver details:',
                    'class' => 'extra-long'
                ],
                'elements' => [
                    'driverFirstname' => [
                        'type' => 'personName',
                        'label' => 'Driver first name:',
                         'class' => 'long'
                    ],
                    'driverLastname' => [
                        'type' => 'personName',
                        'label' => 'Driver last name:',
                        'class' => 'long'
                    ],
                    'vrm' => [
                        'type' => 'vehicleVrm',
                        'label' => 'Vehicle registration mark:',
                        'class' => 'medium'
                    ],
                    'operatorName' => [
                        'type' => 'text',
                        'label' => 'Operator:',
                        'class' => 'medium'
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                        'label' => 'Description:',
                        'class' => 'extra-long'
                    ],
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
