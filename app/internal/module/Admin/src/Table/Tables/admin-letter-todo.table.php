<?php

declare(strict_types=1);

use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Letter Todo',
        'title' => 'Letter Todos',
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
            'title' => 'Todo Key',
            'name' => 'todoKey',
            'sort' => 'todoKey',
            'formatter' => fn($row) => Escape::html($row['todoKey'] ?? ''),
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => fn($row) => Escape::html($row['description'] ?? ''),
        ],
        [
            'title' => 'Help Text',
            'name' => 'helpText',
            'formatter' => function ($row) {
                $text = $row['helpText'] ?? '';
                if (strlen($text) > 80) {
                    $text = substr($text, 0, 80) . '...';
                }
                return Escape::html($text);
            },
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}',
        ],
    ],
];
