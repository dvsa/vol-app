<?php

return [
    'variables' => [
        'empty_message' => false,
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add-previous-licence' => [
                    'label' => 'transport-manager.previouslicences.table.add'
                ]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'transport-manager.previouslicences.table.lic-no',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit-previous-licence'
        ],
        [
            'title' => 'transport-manager.previouslicences.table.holderName',
            'name' => 'holderName',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'licNo',
            'type' => 'ActionLinks',
            'deleteInputName' => 'previousLicences[action][delete-previous-licence][%d]'
        ]
    ]
];
