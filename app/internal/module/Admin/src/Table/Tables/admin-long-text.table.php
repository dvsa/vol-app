<?php

declare(strict_types=1);

use Common\Service\Table\TableBuilder;
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
            'title' => 'UID / Content key',
            'name' => 'translationKey',
            'sort' => 'translationKey',
            'formatter' => fn($row) => Escape::html($row['translationKey']),
        ],
        [
            'title' => 'Page name / Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => fn($row) => Escape::html($row['description'] ?? ''),
        ],
        [
            'title' => '',
            'formatter' => function ($row, $column = []) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $url = $this->urlHelper->fromRoute('admin-dashboard/admin-long-text', [
                    'action' => 'details',
                    'id' => $row['id'],
                ]);
                return sprintf('<a class="govuk-link" href="%s">%s</a>', $url, $this->translator->translate('view'));
            },
        ],
    ],
];
