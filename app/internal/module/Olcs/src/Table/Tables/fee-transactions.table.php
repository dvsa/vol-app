<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\TransactionAmount;
use Common\Service\Table\Formatter\TransactionAmountSum;
use Common\Service\Table\Formatter\TransactionNoAndStatus;

return [
    'variables' => [
        'title' => 'Payments and adjustments',
        'empty_message' => 'There are no transactions',
    ],
    'settings' => [
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'No.',
            'name' => 'transactionId',
            'formatter' => TransactionNoAndStatus::class,
        ],
        [
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => Date::class,
        ],
        [
            'title' => 'Type',
            'name' => 'type',
        ],
        [
            'title' => 'Method',
            'name' => 'method',
        ],
        [
            'title' => 'Processed by',
            'name' => 'processedBy',
        ],
        [
            'title' => 'Allocation',
            'name' => 'amount',
            'formatter' => TransactionAmount::class,
            'isNumeric' => true,
        ],
    ],
    'footer' => [
        'total' => [
            'content' => 'Total',
            'colspan' => 5,
            'align' => 'govuk-!-text-align-right',
        ],
        [
            'formatter' => TransactionAmountSum::class,
            'name' => 'amount',
            'align' => 'govuk-!-text-align-right',
        ],
    ]
];
