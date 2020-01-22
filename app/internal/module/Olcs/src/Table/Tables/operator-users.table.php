<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Users',
    ],
    'columns' => [
        [
            'title' => 'Username',
            'name' => 'loginId',
            'formatter' => function ($row, $col, $sm) {
                $urlHelper = $sm->get('Helper\Url');
                $url = $urlHelper->fromRoute(
                    'admin-dashboard/admin-user-management',
                    [
                        'action' => 'edit',
                        'user' => $row['id']
                    ]
                );
                return '<a href="'.$url.'">'.Escape::html($row['loginId']).'</a>';
            }

        ],
        [
            'title' => 'First name',
            'formatter' => function ($row) {
                return Escape::html($row['contactDetails']['person']['forename']);
            }

        ],
        [
            'title' => 'Last name',
            'formatter' => function ($row) {
                return Escape::html($row['contactDetails']['person']['familyName']);
            }
        ],
        [
            'title' => 'Role',
            'name' => 'role',
            'formatter' => function ($row) {
                return empty($row['roles']) ? 'N/A' : Escape::html($row['roles'][0]['description']);
            }
        ]
    ]
];
