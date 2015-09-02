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
            'formatter' => 'FeeTransactionDate',
        ),
        array(
            'title' => 'Method',
            'stack' => 'transaction->paymentMethod->description',
            'formatter' => 'StackValue',
        ),
        // @todo type was not in AC - remove?
        array(
            'title' => 'Type',
            'stack' => 'transaction->type->description',
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
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
    ),
    'footer' => array(
        'total' => array(
            'content' => 'Total',
            'colspan' => 5,
            'align' => 'right',
        ),
        array(
            'formatter' => 'FeeAmountSum',
            'name' => 'amount',
            'align' => 'right',
        ),
    )
);
