<?php

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
            'formatter' => 'TransactionNoAndStatus',
        ),
        array(
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => 'Date',
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
