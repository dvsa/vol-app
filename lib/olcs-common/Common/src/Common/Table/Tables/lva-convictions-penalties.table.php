<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-tableHeader',
        'within_form' => true,
        'empty_message' => 'selfserve-app-subSection-previous-history-criminal-conviction-tableEmptyMessage'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'Add offence'
                ],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnName',
            'formatter' => Name::class,
            'type' => 'Action',
            'action' => 'edit'
        ],
        [
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnDate',
            'name' => 'convictionDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnOffence',
            'name' => 'categoryText',
        ],
        [
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnNameOfCourt',
            'name' => 'courtFpn',
        ],
        [
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnPenalty',
            'name' => 'penalty',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'ariaDescription' => static fn($row) => $row['forename'] . ' ' . $row['familyName'] . ' ' . $row['categoryText'],
            'deleteInputName' => 'data[table][action][delete][%d]',
        ]
    ]
];
