<?php

return [
    'id' => 'admin-dashboard',
    'label' => 'Admin',
    'route' => 'admin-dashboard',
    'pages' => [
        [
            'id' => 'admin-your-account',
            'label' => 'Your account',
            'route' => 'admin-dashboard/admin-your-account',
            'pages' => [
                [
                    'id' => 'admin-dashboard/admin-your-account/details',
                    'label' => 'Your account',
                    'route' => 'admin-dashboard/admin-your-account/details'
                ],
                [
                    'id' => 'logout',
                    'label' => 'Sign out',
                    'route' => 'auth/logout'
                ]
            ]
        ]
    ]
];
