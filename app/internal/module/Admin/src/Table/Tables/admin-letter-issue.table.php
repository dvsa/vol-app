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
            'formatter' => function($row) {
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
            'formatter' => function($row) {
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
            'formatter' => function($row) {
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
            'title' => 'Goods/PSV',
            'name' => 'goodsOrPsv',
            'formatter' => function($row) {
                // For versioned entity, get goodsOrPsv from current version
                if (isset($row['currentVersion']['goodsOrPsv']['description'])) {
                    return Escape::html($row['currentVersion']['goodsOrPsv']['description']);
                }
                if (isset($row['goodsOrPsv']['description'])) {
                    return Escape::html($row['goodsOrPsv']['description']);
                }
                return 'Both';
            },
        ],
        [
            'title' => 'NI',
            'name' => 'isNi',
            'formatter' => function($row) {
                // For versioned entity, get isNi from current version
                $isNi = false;
                if (isset($row['currentVersion']['isNi'])) {
                    $isNi = $row['currentVersion']['isNi'];
                } elseif (isset($row['isNi'])) {
                    $isNi = $row['isNi'];
                }
                return $isNi ? '<span class="govuk-tag govuk-tag--blue">NI</span>' : '<span class="govuk-tag govuk-tag--grey">GB</span>';
            },
        ],
        [
            'title' => 'Requires Input',
            'name' => 'requiresInput',
            'formatter' => function($row) {
                // For versioned entity, get requiresInput from current version
                $requiresInput = false;
                if (isset($row['currentVersion']['requiresInput'])) {
                    $requiresInput = $row['currentVersion']['requiresInput'];
                } elseif (isset($row['requiresInput'])) {
                    $requiresInput = $row['requiresInput'];
                }
                return $requiresInput ? '<span class="govuk-tag govuk-tag--green">Yes</span>' : '<span class="govuk-tag govuk-tag--grey">No</span>';
            },
        ],
        [
            'title' => 'Current Version',
            'name' => 'currentVersion',
            'formatter' => function($row) {
                if (isset($row['currentVersion']['version'])) {
                    return 'v' . Escape::html($row['currentVersion']['version']);
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