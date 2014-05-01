<?php

return [
    'penalty-comment' => [
        'name' => 'penalty-comment',
        'attributes' => [
            'method' => 'post',
        ],
        'elements' => [
            'notes' => [
                'type' => 'textarea',
                'label' => 'Enter penalties',
                'class' => 'extra-long'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'case' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            'crsf' => [
                'type' => 'crsf',
            ],
            'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
