<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Key Usage & Categories',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Service Interface',
            'name' => 'repository'
        ],
        [
            'title' => 'Path',
            'name' => 'path'
        ],
        [
            'title' => 'Category',
            'formatter' => function ($data, $column) {
                    return empty($data['category']['description']) ? 'N/A' : Escape::html($data['category']['description']);
            }
        ],
        [
            'title' => 'Sub Category',
            'formatter' => function ($data, $column) {
                return empty($data['subCategory']['subCategoryName']) ? 'N/A' : Escape::html($data['subCategory']['subCategoryName']);
            }
        ],
    ]
];
