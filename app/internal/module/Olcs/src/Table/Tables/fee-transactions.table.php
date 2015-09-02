<?php

return array(
    'variables' => array(
        'title' => 'Transactions',
        'empty_message' => 'There are no transactions',
    ),
    'settings' => array(
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Transaction No.',
            'stack' => 'transaction->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'Date',
            'stack' => 'transaction->completedDate',
            'formatter' => 'StackValue', // @todo feeTransactionDate formatter
        ),
        array(
            'title' => 'Method',
            'stack' => 'transaction->paymentMethod->description',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'Processed by',
            'stack' => 'transaction->processedByUser->loginId',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'Allocated',
            'name' => 'amount',
        ),
    )
);
