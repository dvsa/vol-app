<?php

use Common\Service\Table\Formatter\TransportManagerDateOfBirth;
use Common\Service\Table\Formatter\TransportManagerName;

return [
    'variables' => [
        'title' => 'list-of-transport-managers',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [],
                'delete' => [
                    'label' => 'action_links.remove',
                    'requireRows' => true
                ]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Name',
            'formatter' => TransportManagerName::class,
            'name' => 'name'
        ],
        [
            'title' => 'Email',
            'name' => 'email'
        ],
        [
            'title' => 'DOB',
            'name' => 'dob',
            'formatter' => TransportManagerDateOfBirth::class,
        ],
        [
            'name' => 'select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ]
    ]
];
