<?php

use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Users',
        'titleSingular' => 'User',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one'],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Username',
            'name' => 'loginId',
            'formatter' => function ($row, $col) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $url = $this->urlHelper->fromRoute(
                    'admin-dashboard/admin-user-management',
                    [
                        'action' => 'edit',
                        'user' => $row['id']
                    ]
                );
                return '<a class="govuk-link" href="' . $url . '">' . Escape::html($row['loginId']) . '</a>';
            }

        ],
        [
            'title' => 'First name',
            'formatter' => fn($row) => Escape::html($row['contactDetails']['person']['forename'])

        ],
        [
            'title' => 'Last name',
            'formatter' => fn($row) => Escape::html($row['contactDetails']['person']['familyName'])
        ],
        [
            'title' => 'Role',
            'name' => 'role',
            'formatter' => fn($row) => empty($row['roles']) ? 'N/A' : Escape::html($row['roles'][0]['description'])
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
