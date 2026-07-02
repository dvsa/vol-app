<?php

use Common\Service\Table\Formatter\Address;

$translationPrefix = 'application_taxi-phv_licence.table';

return [
    'variables' => [
        'title' => '',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'licence',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'Add Taxi or PHV licence'],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.licence-number',
            'action' => 'edit',
            'name' => 'privateHireLicenceNo',
            'type' => 'Action'
        ],
        [
            'title' => $translationPrefix . '.council',
            'name' => 'councilName'
        ],
        [
            'title' => $translationPrefix . '.address',
            'formatter' => Address::class,
            'name' => 'address'
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'privateHireLicenceNo',
            'type' => 'ActionLinks',
        ],
    ]
];
