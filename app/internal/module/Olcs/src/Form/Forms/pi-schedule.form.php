<?php


return [
    'pi-schedule' => [
        'name' => 'Public inquiry Schedule and Publish',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'elements' => [
            'piNumber' => [
                'type' => 'text',
                'label' => 'PI number'
            ],
            'agreedDate' => [
                'type' => 'dateSelect',
                'label' => 'Agreed date',
                'class' => 'long'
            ],
            'legislation' => [
                'type' => 'select',
                'label' => 'Legislation',
                'class' => 'medium'
            ],
            'venue' => [
                'type' => 'select',
                'label' => 'Venue',
                'class' => 'medium'
            ],
            'otherVenue' => [
                'type' => 'text',
                'label' => 'Other venue',
                'class' => 'long'
            ],
            'piDate' => [
                'type' => 'dateSelect',
                'label' => 'Date of PI',
                'class' => 'long'
            ],
            'presidingTc' => [
                'type' => 'select',
                'label' => 'Presiding TC/DTC/TR/DTR',
                'class' => 'medium'
            ],
            'presidingRole' => [
                'type' => 'select',
                'label' => 'Presiding TC/DTC/TR/DTR role',
                'class' => 'medium'
            ],
            'witnesses' => [
                'type' => 'select',
                'label' => 'Witnesses',
                'class' => 'small'
            ],
            'cancelled' => [
                'type' => 'checkbox',
                'label' => 'Cancelled'
            ],
            'ajourned' => [
                'type' => 'checkbox',
                'label' => 'Ajourned'
            ],
            'definitionsForConsideration' => [
                'type' => 'select',
                'label' => 'Definitions for consideration',
                'class' => 'medium'
            ],
            'detailsToBePublished' => [
                'type' => 'textarea',
                'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                'label' => 'Details to be published',
                'class' => 'extra-long'
            ],
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
            ],
            'cancel' => [
                'name' => 'cancel',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
