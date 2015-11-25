<?php

return [
    'id' => 'admin-dashboard',
    'label' => 'Admin',
    'route' => 'admin-dashboard',
    'pages' => [
        [
            'id' => 'admin-my-account',
            'label' => 'My account',
            'route' => 'admin-dashboard/admin-my-account',
            'pages' => [
                [
                    'id' => 'admin-dashboard/admin-my-account/details',
                    'label' => 'My account',
                    'route' => 'admin-dashboard/admin-my-account/details'
                ],
                [
                    'id' => 'logout',
                    'label' => 'Sign out',
                    'route' => 'logout'
                ]
            ]
        ]
    ]
];
