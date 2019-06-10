<?php

return array(
    'variables' => [
        'title' => 'Interim Refunds',
        'empty_message' => 'Interim refunds are not found by specified filter criteria',
    ],
    'settings' => [
        'showTotal' => true
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Licence Number',
            'sort' => 'l.licNo',
            'formatter' => 'InternalLicenceNumberLink',
        ],
        [
            'title' => 'Operator Name',
            'sort' => 'o.name',
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
            'title' => 'Date Fee Invoiced',
            'sort' => 'invoicedDate',
            'name' => 'invoicedDate',
            'formatter' => 'Date'
        ],
        [
            'title' => 'Fee Amount',
            'sort' => 'amount',
            'name' => 'amount',
            'formatter' => 'FeeAmount'
        ],

        [
            'title' => 'Date of Refund',
            'sort' => 'ftr.createdOn',
            'formatter' => function ($data) {
                $lastTransaction = $data['feeTransactions'][count($data['feeTransactions']) - 1];
                $data = $lastTransaction['amount'] < 0 ? $lastTransaction : [];
                return $this->callFormatter(
                    [
                        'formatter' => 'Date',
                        'name' => 'createdOn',
                    ],
                    $data
                );
            }
        ],
        [
            'title' => 'Refund Status',
            'sort' => 'feeStatus',
            'name' => 'feeStatus',
            'formatter' => 'RefData'
        ],
    ]
);
