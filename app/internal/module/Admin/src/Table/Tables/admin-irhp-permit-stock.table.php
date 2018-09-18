<?php

return [
    'variables' => [
        'title' => 'Permit system settings'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Type',
            'name' => 'irhpPermitTypeId',
            'formatter' => 'IrhpPermitStockType'
        ],
        [
            'title' => 'Validity Period',
            'name' => 'validFrom',
            'formatter' => 'IrhpPermitStockValidity'
        ],
        [
            'title' => 'Quota',
            'name' => 'initialStock',
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
