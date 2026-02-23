<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Appendix',
        'title' => 'Letter Appendices',
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
            'title' => 'Appendix Key',
            'name' => 'appendixKey',
            'sort' => 'appendixKey',
            'formatter' => fn($row) => Escape::html($row['appendixKey'] ?? ''),
        ],
        [
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
            'formatter' => function ($row) {
                if (isset($row['currentVersion']['name'])) {
                    return Escape::html($row['currentVersion']['name']);
                }
                return Escape::html($row['name'] ?? '');
            },
        ],
        [
            'title' => 'Type',
            'name' => 'appendixType',
            'formatter' => function ($row) {
                $type = $row['currentVersion']['appendixType'] ?? $row['appendixType'] ?? 'pdf';
                return $type === 'editable' ? 'Editable' : 'PDF';
            },
        ],
        [
            'title' => 'Current Version',
            'name' => 'currentVersion',
            'formatter' => function ($row) {
                if (isset($row['currentVersion']['versionNumber'])) {
                    return 'v' . Escape::html($row['currentVersion']['versionNumber']);
                }
                return 'v1';
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
