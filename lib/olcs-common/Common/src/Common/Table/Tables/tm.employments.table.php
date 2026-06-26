<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'title' => 'transport-manager.employments.table',
        'within_form' => true,
        'empty_message' => false,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add-employment' => [
                    'label' => 'transport-manager.employments.table.add',
                ]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Employer',
            'name' => 'employerName',
            'type' => 'Action',
            'action' => 'edit-employment',
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
            'deleteInputName' => 'employment[action][delete-employment][%d]'
        ]
    ]
];
