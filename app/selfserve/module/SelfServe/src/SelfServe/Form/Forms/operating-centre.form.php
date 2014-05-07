<?php
return [
    'operating-centre' => [
        'name' => 'authorised-vehicles',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'options' => [
                    'label' => 'Address',
                ],
                // this will ensure the common address fieldset is pulled in
                // to avoid having to redeclare it here
                'type' => 'address',
            ],
            'authorised-vehicles' => [
                'name' => 'authorised-vehicles',
                'options' => [
                    'label' => 'Vehicles & trailers',
                ],
                'elements' => [
                    'no-of-vehicles' => [
                        'type' => 'vehiclesNumber',
                        'label' => 'Total no. of vehicles',
                    ],
                    'no-of-trailers' => [
                        'type' => 'vehiclesNumber',
                        'label' => 'Total no. of trailers',
                    ],
                    'parking-spaces-confirmation' => [
                        'type' => 'checkbox',
                        'label' =>
                            'I have enough parking spaces available for the '.
                            'total number of vehicles and trailers that I want '.
                            'to keep at this address',
                        'options' => [
                            'must_be_checked' => true,
                            'not_checked_message' => 'You must confirm that you have enough parking spaces',
                        ],
                    ],
                    'permission-confirmation' => [
                        'type' => 'checkbox',
                        'label' =>
                            'I am either the site owner or have permission from '.
                            'the site owner to use the premises to park the number '.
                            'of vehicles and trailers stated',
                        'options' => [
                            'must_be_checked' => true,
                            'not_checked_message' => 'You must confirm that you have permission to use the premisses to park the number of vehicles & trailers stated',
                        ],
                    ],
                ]
            ],
        ],
        'elements' => [
            'version' => [
                'type' => 'hidden',
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Save'
            ],
        ]
    ]
];
