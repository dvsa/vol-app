<?php

return [
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Id',
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Placeholder',
            'name' => 'placeholder',
            'sort' => 'placeholder',
        ],
        [
            'title' => 'Replacement Text',
            'name' => 'replacementText',
            'sort' => 'replacementText',
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
