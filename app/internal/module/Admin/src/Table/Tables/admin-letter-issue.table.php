<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Issue',
        'title' => 'Letter Issues',
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
            'title' => 'Issue Key',
            'name' => 'issueKey',
            'sort' => 'issueKey',
            'formatter' => fn($row) => Escape::html($row['issueKey'] ?? ''),
        ],
        [
            'title' => 'Heading',
            'name' => 'heading',
            'sort' => 'heading',
            'formatter' => function ($row) {
                // For versioned entity, get heading from current version
                if (isset($row['currentVersion']['heading'])) {
                    return Escape::html($row['currentVersion']['heading']);
                }
                return Escape::html($row['heading'] ?? '');
            },
        ],
        [
            'title' => 'Category',
            'name' => 'category',
            'formatter' => function ($row) {
                // For versioned entity, get category from current version
                if (isset($row['currentVersion']['category']['description'])) {
                    return Escape::html($row['currentVersion']['category']['description']);
                }
                if (isset($row['category']['description'])) {
                    return Escape::html($row['category']['description']);
                }
                return 'N/A';
            },
        ],
        [
            'title' => 'Sub Category',
            'name' => 'subCategory',
            'formatter' => function ($row) {
                // For versioned entity, get subCategory from current version
                if (isset($row['currentVersion']['subCategory']['subCategoryName'])) {
                    return Escape::html($row['currentVersion']['subCategory']['subCategoryName']);
                }
                if (isset($row['subCategory']['subCategoryName'])) {
                    return Escape::html($row['subCategory']['subCategoryName']);
                }
                return 'N/A';
            },
        ],
        [
            'title' => 'Issue Type',
            'name' => 'letterIssueType',
            'formatter' => function ($row) {
                // For versioned entity, get letterIssueType from current version
                if (isset($row['currentVersion']['letterIssueType']['name'])) {
                    return Escape::html($row['currentVersion']['letterIssueType']['name']);
                }
                if (isset($row['letterIssueType']['name'])) {
                    return Escape::html($row['letterIssueType']['name']);
                }
                return 'N/A';
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
            'title' => 'Publish From',
            'name' => 'publishFrom',
            'formatter' => function ($row, $column) {
                $publishFrom = null;
                if (isset($row['currentVersion']['publishFrom'])) {
                    $publishFrom = $row['currentVersion']['publishFrom'];
                } elseif (isset($row['publishFrom'])) {
                    $publishFrom = $row['publishFrom'];
                }

                if (empty($publishFrom)) {
                    return 'Immediate';
                }

                $column['formatter'] = Date::class;
                $tempRow = ['publishFrom' => $publishFrom];
                return (new Date())->format($tempRow, ['name' => 'publishFrom']);
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
