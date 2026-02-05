<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Test Data',
        'title' => 'Test Data',
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
            'title' => 'Created',
            'name' => 'createdOn',
            'sort' => 'createdOn',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                return empty($row['createdOn']) ? 'N/A' : (new Date())->format($row, $column);
            },
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
