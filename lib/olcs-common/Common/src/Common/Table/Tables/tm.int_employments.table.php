<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'title' => 'transport-manager.employments.table',
        'empty_message' => 'transport-manager.employments.table.empty',
    ],
    'data-group' => 'otherEmployment',
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'transport-manager.employments.table.add'
                ]
            ]
        ],
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
    ],
    'columns' => [
        [
            'title' => 'Employer',
            'name' => 'employerName',
            'type' => 'Action',
            'action' => 'edit',
        ],
        [
            'title' => 'Address',
            'name' => 'contactDetails->address',
            'formatter' => Address::class
        ],
        [
            'title' => 'Position',
            'name' => 'position',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'employerName',
            'type' => 'ActionLinks',
            'deleteInputName' => 'action[delete][%d]'
        ],
    ]
];
