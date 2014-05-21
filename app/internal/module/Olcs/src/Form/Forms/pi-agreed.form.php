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
            'presidingTc' => [
                'type' => 'dateSelect',
                'label' => 'Date of PI',
                 'class' => 'long'
            ],
            'legislation' => [
                'type' => 'select',
                'label' => 'Legislation',
                 'class' => 'long'
            ],
            'agreedDate' => [
                'type' => 'dateSelect',
                'label' => 'Agreed date',
                 'class' => 'long'
            ],
            'presidingTc' => [
                'type' => 'select',
                'label' => 'Agreed by',
                 'class' => 'long'
            ],
            'role' => [
                'type' => 'select',
                'label' => 'Agreed by role',
                 'class' => 'long'
            ],
            'comments' => [
                'type' => 'textarea',
                'filters' => '\Common\Form\Elements\InputFilters\TextMax4000',
                'label' => 'Comments',
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
