<?php

return array(
    'variables' => array(
        'title' => 'pay-fees.table.title',
    ),
    'settings' => array(
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'pay-fees.fee-no',
            'name' => 'id',
        ),
        array(
            'title' => 'pay-fees.description',
            'name' => 'description',
        ),
        array(
            'title' => 'pay-fees.lic-no',
            'formatter' => function ($row, $col, $sm) {
                return $row['licence']['licNo'];
            },
        ),
        array(
            'title' => 'pay-fees.created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'pay-fees.amount',
            'name' => 'amount',
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
    ),
    'footer' => array(
        'total' => array(
            'type' => 'th',
            'content' => 'dashboard-fees-total',
            'formatter' => 'Translate',
            'colspan' => 4
        ),
        array(
            'type' => 'th',
            'formatter' => 'FeeAmountSum',
            'name' => 'amount',
            'align' => 'right',
        ),
    ),
);
