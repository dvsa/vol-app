<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeIdUrl;
use Common\Service\Table\Formatter\TransactionFeeAllocatedAmount;
use Common\Service\Table\Formatter\TransactionFeeStatus;

return [
    'variables' => [
        'title' => 'Fees',
        'titleSingular' => 'Fee',
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Fee No.',
            'name' => 'id',
            'formatter' => FeeIdUrl::class,
        ],
        [
            'title' => 'Description',
            'name' => 'description',
        ],
        [
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'Fee amount',
            'name' => 'amount',
            'formatter' => FeeAmount::class,
            'isNumeric' => true,
        ],
        [
            'title' => 'Allocation',
            'name' => 'allocatedAmount',
            'formatter' => TransactionFeeAllocatedAmount::class,
            'isNumeric' => true,
        ],
        [
            'title' => 'Status',
            'formatter' => TransactionFeeStatus::class,
        ],
    ],
];
