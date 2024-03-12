<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'titleSingular' => 'Case',
        'title' => 'Cases'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Case No.',
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['case' => $row['id'], 'action' => 'details'],
                'case',
                true
            ) . '">' . $row['id'] . '</a>',
            'sort' => 'id'
        ],
        [
            'title' => 'Case type',
            'formatter' => function ($row, $column) {
                if (isset($row['caseType']['description'])) {
                    return $this->translator->translate($row['caseType']['description']);
                } else {
                    return 'Not set';
                }
            },
            'sort' => 'caseType'
        ],
        [
            'title' => 'Created',
            'formatter' => Date::class,
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ],
        [
            'title' => 'Closed',
            'formatter' => Date::class,
            'name' => 'closedDate',
            'sort' => 'closedDate'
        ],
        [
            'title' => 'Description',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'maxlength' => 250,
            'append' => '...',
            'name' => 'description'
        ],
        [
            'title' => 'ECMS',
            'name' => 'ecmsNo'
        ],
        [
            'title' => 'markup-table-th-action', //this is a translation key
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
