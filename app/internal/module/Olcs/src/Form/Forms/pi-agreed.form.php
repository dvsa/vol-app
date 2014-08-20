<?php


return [
    'pi-agreed' => [
        'name' => 'Public inquiry Agreed and Legislation',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'elements' => [
            'piType' => [
                'type' => 'select',
                'label' => 'Type of PI'
            ],
            'caseworker' => [
                'type' => 'select',
                'label' => 'Caseworker assigned to',
                 'class' => 'medium'
            ],
            'legislation' => [
                'type' => 'select',
                'label' => 'Legislation',
                 'class' => 'medium'
            ],
            'agreedDate' => [
                'type' => 'dateSelect',
                'label' => 'Agreed date'
            ],
            'presidingTc' => [
                'type' => 'select',
                'label' => 'Agreed by',
                 'class' => 'medium'
            ],
            'role' => [
                'type' => 'select',
                'label' => 'Agreed by role',
                 'class' => 'medium'
            ],
            'comments' => [
                'type' => 'textarea',
                'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                'label' => 'Comments',
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
