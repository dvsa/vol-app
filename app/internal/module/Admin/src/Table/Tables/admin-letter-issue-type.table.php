<?php

declare(strict_types=1);

use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Issue Type',
        'title' => 'Letter Issue Types',
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
            'title' => 'Code',
            'name' => 'code',
            'sort' => 'code',
            'formatter' => fn($row) => Escape::html($row['code'] ?? ''),
        ],
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
            'title' => 'Display Order',
            'name' => 'displayOrder',
            'sort' => 'displayOrder',
            'formatter' => fn($row) => Escape::html($row['displayOrder'] ?? ''),
        ],
        [
            'title' => 'Active',
            'name' => 'isActive',
            'formatter' => function ($row) {
                $isActive = $row['isActive'] ?? false;
                return $isActive
                    ? '<span class="govuk-tag govuk-tag--green">Active</span>'
                    : '<span class="govuk-tag govuk-tag--grey">Inactive</span>';
            },
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}',
        ],
    ],
];
