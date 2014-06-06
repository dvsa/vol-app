<?php


return [
    'pi-decision' => [
        'name' => 'Public inquiry Register Decision',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'elements' => [
            'piNumber' => [
                'type' => 'text',
                'label' => 'PI number'
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
            'piReason' => [
                'type' => 'text',
                'label' => 'Reason for PI',
                'class' => 'medium'
            ],
            'witnesses' => [
                'type' => 'select',
                'label' => 'Witnesses',
                'class' => 'small'
            ],
            'decisionDate' => [
                'type' => 'dateSelect',
                'label' => 'Date of decision'
            ],
            'notificationDate' => [
                'type' => 'dateSelect',
                'label' => 'Date of notification'
            ],
            'licenceRevoked' => [
                'type' => 'checkbox',
                'label' => 'Licence revoked due to public inquiry'
            ],
            'definitionCategory' => [
                'type' => 'select',
                'label' => 'Definition category',
                'class' => 'medium'
            ],
            'definition' => [
                'type' => 'select',
                'label' => 'Definition',
                'class' => 'medium'
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
