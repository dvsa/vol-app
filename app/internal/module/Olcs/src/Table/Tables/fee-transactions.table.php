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
            'name' => 'transactionId',
            'formatter' => 'TransactionNoAndStatus',
        ),
        array(
            'title' => 'Type',
            'name' => 'type',
        ),
        array(
            'title' => 'Date',
            'name' => 'completedDate',
            'formatter' => 'Date',
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
            'title' => 'Allocated',
            'name' => 'amount',
            'formatter' => 'TransactionAmount',
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
            'formatter' => 'TransactionAmountSum',
            'name' => 'amount',
            'align' => 'right',
        ),
    )
);
