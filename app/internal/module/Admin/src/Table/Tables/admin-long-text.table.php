<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Long Text',
        'titleSingular' => 'Long Text',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'edit' => [
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'requireRows' => true,
                ],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'ID',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            // What a developer copies to place this content on a page.
            'title' => 'UID',
            'name' => 'referenceKey',
            'sort' => 'referenceKey',
            'formatter' => fn($row) => Escape::html($row['referenceKey'] ?? ''),
        ],
        [
            'title' => 'Page name',
            'name' => 'pageName',
            'sort' => 'pageName',
            'formatter' => fn($row) => Escape::html($row['pageName'] ?? ''),
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => fn($row) => Escape::html($row['description'] ?? ''),
        ],
        [
            'title' => 'Language',
            'name' => 'locale',
            'sort' => 'locale',
            'formatter' => fn($row) => Escape::html($row['locale'] ?? ''),
        ],
        [
            'title' => 'Last updated',
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
