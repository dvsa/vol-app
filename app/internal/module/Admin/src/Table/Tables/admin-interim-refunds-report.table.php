<?php

return array(
    'variables' => [
        'title' => 'Interim Refunds'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Licence Number',
            'formatter' => 'InternalLicenceNumberLink',
        ],
        [
            'title' => 'Operator Name',
            'formatter' => function ($data) {
                return $this->callFormatter(
                    [
                        'formatter' => 'OrganisationLink',
                    ],
                    $data['licence']
                );
            }
        ],
        [
            'title' => 'Date Fee Received',
            'name' => 'invoicedDate',
            'formatter' => 'Date'
        ],
        [
            'title' => 'Fee Amount',
            'name' => 'amount',
            'formatter' => 'FeeAmount'
        ],

        [
            'title' => 'Date of Refund',
            'name' => 'refundDate',
        ],
        [
            'title' => 'Refund Status',
            'name' => 'feeStatus',
            'formatter' => 'RefData'
        ],
    ]
);
