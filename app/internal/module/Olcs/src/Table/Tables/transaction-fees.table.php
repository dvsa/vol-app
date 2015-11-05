<?php

return array(
    'variables' => array(
        'title' => 'Fees',
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Fee No.',
            'name' => 'id',
            'formatter' => 'FeeIdUrl',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Fee amount',
            'name' => 'amount',
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
        array(
            'title' => 'Allocated',
            'name' => 'allocatedAmount',
            'formatter' => 'TransactionFeeAllocatedAmount',
            'align' => 'right',
        ),
        array(
            'title' => 'Status',
            'formatter' => 'TransactionFeeStatus',
        ),
    ),
);
