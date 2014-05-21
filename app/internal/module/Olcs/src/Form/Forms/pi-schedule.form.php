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
                'label' => 'Date of PI',
                'class' => 'long'
            ],
            'legislation' => [
                'type' => 'select',
                'label' => 'Legislation',
                'class' => 'long'
            ],
            'venue' => [
                'type' => 'select',
                'label' => 'Venue',
                'class' => 'long'
            ],
            'otherVenue' => [
                'type' => 'text',
                'label' => 'Other venue',
                 'class' => 'long'
            ],
            'presidingTc' => [
                'type' => 'select',
                'label' => 'Presiding TC/DTC/TR/DTR',
                 'class' => 'long'
            ],
            'presidingRole' => [
                'type' => 'select',
                'label' => 'Presiding TC/DTC/TR/DTR role',
                 'class' => 'long'
            ],
            'witnesses' => [
                'type' => 'select',
                'label' => 'Presiding TC/DTC/TR/DTR role',
                 'class' => 'long'
            ],
            'cancelled' => [
                'type' => 'checkbox',
                'label' => 'Presiding TC/DTC/TR/DTR role',
                 'class' => 'long'
            ],
            'ajourned' => [
                'type' => 'checkbox',
                'label' => 'Ajourned'
            ],
            'detailsToBePublished' => [
                'type' => 'textarea',
                'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                'label' => 'Details to be published',
                'class' => 'extra-long'
            ],
            'vosaCase' => [
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
