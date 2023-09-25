<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeIdUrl;
use Common\Service\Table\Formatter\TransactionFeeAllocatedAmount;
use Common\Service\Table\Formatter\TransactionFeeStatus;

return array(
    'variables' => array(
        'title' => 'Fees',
        'titleSingular' => 'Fee',
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Fee No.',
            'name' => 'id',
            'formatter' => FeeIdUrl::class,
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => Date::class,
        ),
        array(
            'title' => 'Fee amount',
            'name' => 'amount',
            'formatter' => FeeAmount::class,
            'isNumeric' => true,
        ),
        array(
            'title' => 'Allocation',
            'name' => 'allocatedAmount',
            'formatter' => TransactionFeeAllocatedAmount::class,
            'isNumeric' => true,
        ),
        array(
            'title' => 'Status',
            'formatter' => TransactionFeeStatus::class,
        ),
    ),
);
