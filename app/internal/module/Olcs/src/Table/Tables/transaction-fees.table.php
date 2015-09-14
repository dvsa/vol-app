<?php

return array(
    'variables' => array(
        'title' => 'Fees',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'partRefund' => array(
                    'class' => 'primary js-require--multiple',
                    'value' => 'partRefund',
                    'label' => 'Part refund',
                    'requireRows' => true
                ),
            )
        ),
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
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'type' => 'Checkbox',
        )
    ),
);
