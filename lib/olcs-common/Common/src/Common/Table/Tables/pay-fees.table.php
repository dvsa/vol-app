<?php

use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeAmountSum;
use Common\Service\Table\Formatter\Translate;

return [
    'variables' => [
        'title' => 'pay-fees.table.title',
    ],
    'settings' => [
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'pay-fees.description',
            'name' => 'description',
        ],
        [
            'title' => 'pay-fees.reference',
            'formatter' => static fn($row, $col) => $row['licence']['licNo'],
        ],
        [
            'title' => 'pay-fees.amountt',
            'isNumeric' => true,
            'name' => 'amount',
            'formatter' => FeeAmount::class,
        ],
        [
            'title' => 'pay-fees.outstandingg',
            'isNumeric' => true,
            'name' => 'outstanding',
            'formatter' => FeeAmount::class,
        ],
    ],
    'footer' => [
        'total' => [
            'type' => 'th',
            'content' => 'dashboard-fees-total',
            'formatter' => Translate::class,
            'colspan' => 3,
        ],
        [
            'type' => 'th',
            'formatter' => FeeAmountSum::class,
            'name' => 'outstanding',
            'align' => 'govuk-!-text-align-right',
        ],
    ],
];
