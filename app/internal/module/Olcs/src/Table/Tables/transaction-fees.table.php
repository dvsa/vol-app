<?php

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
            'isNumeric' => true,
        ),
        array(
            'title' => 'Allocation',
            'name' => 'allocatedAmount',
            'formatter' => 'TransactionFeeAllocatedAmount',
            'isNumeric' => true,
        ),
        array(
            'title' => 'Status',
            'formatter' => 'TransactionFeeStatus',
        ),
    ),
);
