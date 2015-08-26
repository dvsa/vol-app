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
            'title' => 'Amount',
            'name' => 'amount',
            'sort' => 'amount'
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => 'Date',
            'sort' => 'invoicedDate'
        ),
        // @todo
        // temporarily removed sort options during OLCS-10407 refactoring,
        // add back in once payment functionality complete
        //
        array(
            'title' => 'Receipt No.',
            'name' => 'receiptNo',
            // 'sort' => 'receiptNo'
        ),
        array(
            'title' => 'Received',
            'name' => 'receivedDate',
            'formatter' => 'Date',
            // 'sort' => 'receivedDate'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
