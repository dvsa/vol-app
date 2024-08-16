<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => ' open cases associated with this licence'
    ],
    'attributes' => [
        'name' => 'openCases'
    ],
    'settings' => [
        'showTotal' => true
    ],
    'columns' => [
        [
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a class="govuk-link" href="' . $this->generateUrl(
                    ['case' => $row['id'], 'action' => 'details'],
                    'case',
                    true
                ) . '">' . $row['id'] . '</a>',
            'sort' => 'id'
        ],
        [
            'title' => 'Case type',
            'formatter' => function ($row, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
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
            'title' => 'Description',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'maxlength' => 250,
            'append' => '...',
            'name' => 'description'
        ],
    ]
];
