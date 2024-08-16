<?php

use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'Team',
        'title' => 'Teams'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $routeParams = ['team' => $row['id'], 'action' => 'edit'];
                $route = 'admin-dashboard/admin-team-management';
                $url = $this->generateUrl($routeParams, $route);
                return '<a class="govuk-link" href="' . $url . '">' . $row['name'] . '</a>';
            },
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
