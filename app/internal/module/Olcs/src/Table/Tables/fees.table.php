<?php

return array(
    'variables' => array(
        'title' => 'Fees',
        'titleSingular' => 'Fee'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'formName' => 'fees',
            'actions' => array(
                'new' => array('class' => 'secondary', 'value' => 'New', 'requireRows' => false),
                'pay' => array('class' => 'primary js-require--multiple', 'value' => 'Pay', 'requireRows' => true),
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Fee No.',
            'sort' => 'id',
            'name' => 'id',
            'formatter' => 'FeeNoAndStatus',
        ),
        array(
            'title' => 'Description',
            'formatter' => 'FeeUrl',
            'sort' => 'description',
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
            'sort' => 'invoicedDate'
        ),
        array(
            'title' => 'Latest payment ref.',
            'name' => 'receiptNo',
        ),
        array(
            'title' => 'Fee amount',
            'name' => 'amount',
            'sort' => 'grossAmount',
            'formatter' => 'FeeAmount',
        ),
        array(
            'title' => 'Outstanding',
            'name' => 'outstanding',
            'formatter' => 'Money',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'type' => 'Checkbox',
        ),
    )
);
