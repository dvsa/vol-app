<?php

return [
    'label' => 'Admin',
    'route' => 'admin-dashboard',
    'pages' => [
        [
            'label' => 'Printing',
            'route' => 'admin-dashboard/admin-printing',
        ],
        [
            'label' => 'Disc Printing',
            'route' => 'admin-dashboard/admin-disc-printing',
        ],
        [
            'label' => 'Scanning',
            'route' => 'admin-dashboard/admin-scanning',
        ],
        [
            'id'    => 'admin-dashboard/admin-publication',
            'label' => 'Publications',
            'route' => 'admin-dashboard/admin-publication',
            'pages' => [
                [
                    'id' => 'admin-dashboard/admin-publication/published',
                    'label' => 'Published',
                    'action' => 'admin-dashboard',
                ],
                [
                    'id' => 'admin-dashboard/admin-publication/recipients',
                    'label' => 'Recipients',
                    'action' => 'admin-dashboard',
                ]
            ]
        ],
        [
            'label' => 'Continuations',
            'route' => 'admin-dashboard/admin-continuation',
        ],
        [
            'label' => 'Reports',
            'route' => 'admin-dashboard/admin-report',
        ],
        [
            'label' => 'User management',
            'route' => 'admin-dashboard/admin-user-management',
        ],
        [
            'label' => 'Financial standing rates',
            'route' => 'admin-dashboard/admin-financial-standing',
        ],
        [
            'label' => 'Public holidays',
            'route' => 'admin-dashboard/admin-public-holiday',
        ],
        [
            'label' => 'System messages',
            'route' => 'admin-dashboard/admin-system-message',
        ]
    ]
];
