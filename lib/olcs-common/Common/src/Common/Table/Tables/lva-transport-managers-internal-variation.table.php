<?php

use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\TransportManagerDateOfBirth;
use Common\Service\Table\Formatter\TransportManagerName;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'list-of-transport-managers',
        'within_form' => true,
        'empty_message' => 'lva-transport-manager-table-empty-message'
    ],
    'settings' => [
        'crud' => [
            'actions' => []
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
            'lva' => 'variation',
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
            'type' => 'DeltaActionLinks',
        ],
    ]
];
