<?php

return [
    'conviction-comment' => [
        'name' => 'conviction-comment',
        'attributes' => [
            'method' => 'post',
        ],
        'elements' => [
            'convictionData' => [
                'type'  => 'textarea',
                'label' => 'Convictions from PLS',
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
            ]
        ]
    ]
];
