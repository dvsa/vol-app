<?php

use Common\Service\Table\Formatter\Date;

$prefix = 'selfserve-app-subSection-previous-history-previous-licence-';

return [
    'variables' => [
        'title' => $prefix . 'tableHeader',
        'within_form' => true,
        'empty_message' => false
    ],
    'settings' => [
        'crud' => [
            'formName' => 'held',
            'actions' => [
                'add' => ['label' => 'Add licence details'],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => $prefix . 'columnLicNo',
            'name' => 'licNo',
            'type' => 'Action',
            'action' => 'edit'
        ],
        [
            'title' => $prefix . 'columnHolderName',
            'name' => 'holderName',
        ],
        [
            'title' => $prefix . 'columnPurchaseDate',
            'name' => 'purchaseDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'licNo',
            'type' => 'ActionLinks',
            'deleteInputName' => 'assets[prevPurchasedAssets-table][action][delete][%d]'
        ]
    ]
];
