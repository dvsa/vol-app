<?php

return array(
    'variables' => array(
        'title' => 'dashboard-fees.table.title',
        'empty_message' => 'dashboard-fees-empty-message',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'fees',
            'actions' => array(
                'pay' => array('class' => 'primary js-require--multiple', 'value' => 'Pay', 'requireRows' => true),
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Fee No.',
            'name' => 'id',
            'formatter' => 'FeeStatus',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
        ),
        array(
            'title' => 'Licence No.',
            'name' => 'licNo',
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Amount',
            'name' => 'amount',
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
        array(
            'title' => '',
            'type' => 'Checkbox',
            'width' => 'checkbox',
        )
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
        'remainingColspan' => array(
            'type' => 'th',
            'colspan' => 1
        ),
    ),
);
