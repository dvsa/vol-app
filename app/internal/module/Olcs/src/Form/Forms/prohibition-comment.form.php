<?php

return [
    'prohibition-comment' => [
        'name' => 'prohibition-comment',
        'attributes' => [
            'method' => 'post',
        ],
        'elements' => [
            'notes' => [
                'type' => 'textarea',
                'label' => 'Enter prohibition notes',
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
            'submit' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
        ]
    ]
];
