<?php

declare(strict_types=1);

use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Choice',
        'title' => 'Letter Choices',
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
            'title' => 'Choice Key',
            'name' => 'choiceKey',
            'sort' => 'choiceKey',
            'formatter' => fn($row) => Escape::html($row['choiceKey'] ?? ''),
        ],
        [
            'title' => 'Label',
            'name' => 'label',
            'sort' => 'label',
            'formatter' => fn($row) => Escape::html($row['label'] ?? ''),
        ],
        [
            'title' => 'Group',
            'name' => 'groupLabel',
            'formatter' => fn($row) => Escape::html($row['groupLabel'] ?? ''),
        ],
        [
            'title' => 'Input Type',
            'name' => 'inputType',
            'formatter' => fn($row) => Escape::html($row['inputType'] ?? ''),
        ],
        [
            'title' => 'Active',
            'name' => 'isActive',
            'formatter' => function ($row) {
                $isActive = $row['isActive'] ?? false;
                return $isActive
                    ? '<span class="govuk-tag govuk-tag--green">Yes</span>'
                    : '<span class="govuk-tag govuk-tag--grey">No</span>';
            },
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}',
        ],
    ],
];
