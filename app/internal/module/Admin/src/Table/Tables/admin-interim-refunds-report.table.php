<?php

return array(
    'variables' => array(
        'title' => 'Interim Refunds'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Licence Number',
            'name' => 'licenceNo'
        ),
        array(
            'title' => 'Operator Name',
            'name' => 'operator',
        ),
        array(
            'title' => 'Date Fee Received',
            'name' => 'feeDate',
        ),
        array(
            'title' => 'Fee Amount',
            'name' => 'amount',
        ),

        array(
            'title' => 'Date of Refund',
            'name' => 'refundDate',
        ),
        array(
            'title' => 'Refund Status',
            'name' => 'status',
        ),
    )
);
