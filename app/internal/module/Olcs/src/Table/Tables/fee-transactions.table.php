<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\TransactionAmount;
use Common\Service\Table\Formatter\TransactionAmountSum;
use Common\Service\Table\Formatter\TransactionNoAndStatus;

return array(
    'variables' => array(
        'title' => 'Payments and adjustments',
        'empty_message' => 'There are no transactions',
    ),
    'settings' => array(
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'No.',
            'name' => 'transactionId',
            'formatter' => TransactionNoAndStatus::class,
        ),
        array(
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => Date::class,
        ),
        array(
            'title' => 'Type',
            'name' => 'type',
        ),
        array(
            'title' => 'Method',
            'name' => 'method',
        ),
        array(
            'title' => 'Processed by',
            'name' => 'processedBy',
        ),
        array(
            'title' => 'Allocation',
            'name' => 'amount',
            'formatter' => TransactionAmount::class,
            'isNumeric' => true,
        ),
    ),
    'footer' => array(
        'total' => array(
            'content' => 'Total',
            'colspan' => 5,
            'align' => 'govuk-!-text-align-right',
        ),
        array(
            'formatter' => TransactionAmountSum::class,
            'name' => 'amount',
            'align' => 'govuk-!-text-align-right',
        ),
    )
);
