<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'empty_message' => false,
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add-previous-conviction' => [
                    'label' => 'transport-manager.convictionsandpenalties.table.add',
                ],
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'transport-manager.convictionsandpenalties.table.conviction-date',
            'name' => 'convictionDate',
            'formatter' => Date::class,
            'type' => 'Action',
            'action' => 'edit-previous-conviction'
        ],
        [
            'title' => 'transport-manager.convictionsandpenalties.table.offence',
            'name' => 'categoryText',
        ],
        [
            'title' => 'transport-manager.convictionsandpenalties.table.name-of-court',
            'name' => 'courtFpn',
        ],
        [
            'title' => 'transport-manager.convictionsandpenalties.table.penalty',
            'name' => 'penalty',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'categoryText',
            'type' => 'ActionLinks',
            'deleteInputName' => 'convictions[action][delete-previous-conviction][%d]'
        ]
    ]
];
