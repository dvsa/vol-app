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
            'formatter' => function ($row) {
                $data = $row['currentVersion']['description'] ?? $row['description'] ?? '';
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                if (!is_array($data) || empty($data['blocks'])) {
                    return Escape::html(is_string($data) ? $data : '');
                }
                $text = implode(' ', array_map(
                    fn($block) => strip_tags($block['data']['text'] ?? ''),
                    $data['blocks']
                ));
                if (strlen($text) > 120) {
                    $text = substr($text, 0, 120) . '...';
                }
                return Escape::html($text);
            },
        ],
        [
            'title' => 'Help Text',
            'name' => 'helpText',
            'formatter' => function ($row) {
                $text = $row['currentVersion']['helpText'] ?? $row['helpText'] ?? '';
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
