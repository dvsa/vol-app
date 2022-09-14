<?php

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
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
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
            'formatter' => function ($row, $col, $sm) {
                $urlHelper = $sm->get('Helper\Url');
                $url = $urlHelper->fromRoute(
                    'admin-dashboard/admin-user-management',
                    [
                        'action' => 'edit',
                        'user' => $row['user']['id']
                    ]
                );
                return '<a class="govuk-link" href="'.$url.'">'.Escape::html($row['user']['loginId']).'</a>';
            }
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
