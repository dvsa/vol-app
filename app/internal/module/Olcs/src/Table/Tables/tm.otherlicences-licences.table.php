<?php

use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'title' => 'transport-manager.otherlicences.table',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add-other-licence-licences' => ['label' => 'Add', 'class' => 'govuk-button'],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'transport-manager.otherlicences.table.lic_no',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit-other-licence-licences'
        ],
        [
            'title' => 'transport-manager.otherlicences.table.role',
            'name' => 'role',
            'formatter' => RefData::class
        ],
        [
            'title' => 'transport-manager.otherlicences.table.operating_centres',
            'name' => 'operatingCentres',
        ],
        [
            'title' => 'transport-manager.otherlicences.table.total_auth_vehicles',
            'isNumeric' => true,
            'name' => 'totalAuthVehicles',
        ],
        [
            'title' => 'transport-manager.otherlicences.table.hours_per_week',
            'isNumeric' => true,
            'name' => 'hoursPerWeek',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-other-licence-licences][%d]'
        ],
    ]
];
