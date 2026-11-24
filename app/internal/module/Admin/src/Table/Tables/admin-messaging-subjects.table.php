<?php

use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [
        'title' => 'Message categories',
    ],
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
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
        ],
        [
            'title' => 'Category',
            'formatter' => fn($row) => \Common\Util\Escape::html($row['category']['description']),
        ],
        [
            'title' => 'Sub-category',
            'formatter' => fn($row) => \Common\Util\Escape::html($row['subCategory']['subCategoryName'] ?? 'Not applicable'),
        ],
        [
            'title' => 'Active',
            'name' => 'isActive',
            'formatter' => YesNo::class,
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
