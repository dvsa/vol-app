<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Type',
        'title' => 'Letter Types',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false,
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one',
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
            'formatter' => fn($row) => Escape::html($row['name'] ?? ''),
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'formatter' => fn($row) => Escape::html($row['description'] ?? ''),
        ],
        [
            'title' => 'Master Template',
            'name' => 'masterTemplate',
            'formatter' => fn($row) => Escape::html($row['masterTemplate']['name'] ?? 'N/A'),
        ],
        [
            'title' => 'Category',
            'name' => 'category',
            'formatter' => fn($row) => Escape::html($row['category']['description'] ?? 'N/A'),
        ],
        [
            'title' => 'Sub Category',
            'name' => 'subCategory',
            'formatter' => fn($row) => Escape::html($row['subCategory']['subCategoryName'] ?? 'N/A'),
        ],
        [
            'title' => 'Active',
            'name' => 'isActive',
            'sort' => 'isActive',
            'formatter' => fn($row) => $row['isActive'] ? '<span class="govuk-tag govuk-tag--green">Active</span>' : '<span class="govuk-tag govuk-tag--grey">Inactive</span>',
        ],
        [
            'title' => 'Last Modified',
            'name' => 'lastModifiedOn',
            'sort' => 'lastModifiedOn',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                return empty($row['lastModifiedOn']) ? 'N/A' : (new Date())->format($row, $column);
            },
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}',
        ],
    ],
];