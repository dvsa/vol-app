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
                'label' => 'Enter prohibitions',
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
            'cancel' => [
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
