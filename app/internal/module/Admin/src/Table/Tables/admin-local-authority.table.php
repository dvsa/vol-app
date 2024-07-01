<?php

return [
    'variables' => [
        'titleSingular' => 'Local Authority',
        'title' => 'Local Authorities'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one']
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
            'title' => 'ID',
            'name' => 'id',
        ],
        [
            'title' => 'Description',
            'name' => 'description',
        ],
        [
            'title' => 'Email Address',
            'name' => 'emailAddress',
        ],
        [
            'title' => 'TransXchange Name',
            'name' => 'txcName',
        ],
        [
            'title' => 'NAPTAN Code',
            'name' => 'naptanCode',
        ],
        [
            'title' => 'Traffic Area',
            'name' => 'trafficArea',
            'formatter' => fn($row) => $row['trafficArea']['name']
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
