<?php

return [
    'id' => 'admin-dashboard',
    'label' => 'Admin',
    'route' => 'admin-dashboard',
    'pages' => [
        [
            'id' => 'admin-my-details',
            'label' => 'My details',
            'route' => 'admin-dashboard/admin-my-details',
            'pages' => [
                [
                    'id' => 'admin-dashboard/admin-my-details/details',
                    'label' => 'My details',
                    'route' => 'admin-dashboard/admin-my-details/details'
                ],
                [
                    'id' => 'admin-dashboard/admin-my-details/sign-out',
                    'label' => 'Sign out',
                    'route' => 'admin-dashboard/admin-my-details/details'
                ]
            ]
        ]
    ]
];
