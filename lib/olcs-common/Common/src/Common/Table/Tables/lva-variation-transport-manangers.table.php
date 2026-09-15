<?php

use Common\Service\Table\Formatter\TransportManagerDateOfBirth;
use Common\Service\Table\Formatter\TransportManagerName;

return [
    'variables' => [
        'title' => '',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [],
                'delete' => [
                    'label' => 'action_links.remove',
                    'requireRows' => true
                ],
                'restore' => [
                    'requireRows' => true
                ],
            ]
        ],
        'row-disabled-callback' => static fn($row) => isset($row['action']) && in_array($row['action'], ['D', 'C'])
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Name',
            'formatter' => TransportManagerName::class,
            'internal' => true,
            'lva' => 'variation'
        ],
        [
            'title' => 'Email',
            'name' => 'email'
        ],
        [
            'title' => 'DOB',
            'name' => 'dob',
            'formatter' => TransportManagerDateOfBirth::class,
            'internal' => true,
            'lva' => 'variation'
        ],
        [
            'name' => 'select',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => [
                'action'
            ]
        ]
    ]
];
