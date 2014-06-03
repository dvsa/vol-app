<?php

return [
    'annual-test-history-comment' => [
        'name' => 'annual-test-history-comment',
        'attributes' => [
            'method' => 'post',
        ],
        'elements' => [
            'annualTestHistory' => [
                'type' => 'textarea',
                'label' => 'Enter annual test history',
                'class' => 'extra-long'
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
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
