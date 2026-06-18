<?php

use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\TransportManagerDateOfBirth;
use Common\Service\Table\Formatter\TransportManagerName;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'list-of-transport-managers',
        'within_form' => true,
        'empty_message' => 'lva-transport-manager-licence-table-empty-message'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Name',
            'formatter' => TransportManagerName::class,
            'internal' => true,
            'lva' => 'licence',
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
            'lva' => 'licence',
        ],
        [
            'title' => 'markup-table-th-remove-restore', //view partial from olcs-common
            'ariaDescription' => function ($row, $column) {
                $column['formatter'] = Name::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['name']);
            },
            'type' => 'DeltaActionLinks'
        ],
    ]
];
