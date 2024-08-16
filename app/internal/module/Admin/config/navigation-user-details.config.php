<?php

return [
    'id' => 'admin-dashboard',
    'label' => 'Admin',
    'route' => 'admin-dashboard',
    'pages' => [
        [
            'id' => 'admin-your-account',
            'class' => 'govuk-link--no-visited-state',
            'label' => 'Your account',
            'route' => 'admin-dashboard/admin-your-account',
            'pages' => [
                [
                    'id' => 'admin-dashboard/admin-your-account/details',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Your account',
                    'route' => 'admin-dashboard/admin-your-account/details'
                ],
                [
                    'id' => 'logout',
                    'class' => 'govuk-link--no-visited-state',
                    'label' => 'Sign out',
                    'route' => 'auth/logout'
                ]
            ]
        ]
    ]
];
