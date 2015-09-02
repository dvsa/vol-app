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
            'formatter' => 'FeeStatus',
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
            'sort' => 'amount'
        ),

        array(
            'title' => 'Outstanding',
            'name' => 'outstanding',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
