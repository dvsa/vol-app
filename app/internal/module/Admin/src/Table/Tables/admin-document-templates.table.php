<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Document Templates',
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
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
            'formatter' => function ($row) {
                $column['formatter'] = 'DocumentDescription';
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ],
        [
            'title' => 'Category',
            'name' => 'category',
            'formatter' => function ($row) {
                return empty($row['category']['description']) ? '' : Escape::html($row['category']['description']);
            },
        ],
        [
            'title' => 'Subcategory',
            'name' => 'subCategory',
            'formatter' => function ($row) {
                return empty($row['subCategory']['subCategoryName']) ? '' : Escape::html($row['subCategory']['subCategoryName']);
            },
        ],
        [
        'title' => 'Identifier',
        'name' => 'filename',
        'formatter' => function ($row) {
            return Escape::html($row['document']['identifier']);
        },
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
