<?php

use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'within_form' => true,
        'empty_message' => false,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add-other-licence-applications' => [
                    'label' => 'transport-manager.otherlicences.table.add',
                ],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'transport-manager.otherlicences.table.lic_no',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit-other-licence-applications'
        ],
        [
            'title' => 'transport-manager.otherlicences.table.role',
            'name' => 'role',
            'formatter' => RefData::class
        ],
        [
            'title' => 'transport-manager.otherlicences.table.total_auth_vehicles',
            'name' => 'totalAuthVehicles',
        ],
        [
            'title' => 'transport-manager.otherlicences.table.hours_per_week',
            'name' => 'hoursPerWeek',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'licNo',
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-other-licence-applications][%d]'
        ]
    ]
];
