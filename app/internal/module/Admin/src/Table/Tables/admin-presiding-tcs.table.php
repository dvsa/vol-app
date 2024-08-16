<?php

use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Presiding TCs',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
        ],
        [
            'title' => 'User',
            'name' => 'userId',
            'sort' => 'userId',
            'formatter' => function ($row, $col) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $url = $this->urlHelper->fromRoute(
                    'admin-dashboard/admin-user-management',
                    [
                        'action' => 'edit',
                        'user' => $row['user']['id']
                    ]
                );

                return '<a class="govuk-link" href="' . $url . '">' . Escape::html($row['user']['loginId']) . '</a>';
            }
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
