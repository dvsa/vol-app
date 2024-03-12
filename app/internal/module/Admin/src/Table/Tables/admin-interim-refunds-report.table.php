<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\InternalLicenceNumberLink;
use Common\Service\Table\Formatter\OrganisationLink;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'title' => 'Interim Refunds',
        'titleSingular' => 'Interim Refund',
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
            'formatter' => InternalLicenceNumberLink::class,
        ],
        [
            'title' => 'Operator Name',
            'sort' => 'o.name',
            'formatter' => fn($data) => $this->callFormatter(
                [
                    'formatter' => OrganisationLink::class,
                ],
                $data['licence']
            )
        ],
        [
            'title' => 'Date Fee Invoiced',
            'sort' => 'invoicedDate',
            'name' => 'invoicedDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'Fee Amount',
            'isNumeric' => true,
            'name' => 'amount',
            'formatter' => FeeAmount::class
        ],

        [
            'title' => 'Date of Refund',
            'sort' => 'ftr.createdOn',
            'formatter' => function ($data) {
                $refundTransaction = array_filter(
                    $data['feeTransactions'],
                    fn($transaction) => $transaction['amount'] < 0
                );
                $refundTransaction = array_shift($refundTransaction);
                return $this->callFormatter(
                    [
                        'formatter' => Date::class,
                        'name' => 'createdOn',
                    ],
                    $refundTransaction
                );
            }
        ],
        [
            'title' => 'Refund Status',
            'sort' => 'feeStatus',
            'name' => 'feeStatus',
            'formatter' => RefData::class
        ],
    ]
];
