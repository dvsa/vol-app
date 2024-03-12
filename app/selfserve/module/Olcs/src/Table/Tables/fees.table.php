<?php

use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeAmountSum;
use Common\Service\Table\Formatter\FeeUrlExternal;
use Common\Service\Table\Formatter\Translate;

return [
    'variables' => [
        'title' => 'dashboard-fees.table.title',
        'empty_message' => 'dashboard-fees-empty-message',
    ],
    'settings' => [
        'crud' => [
            'formName' => 'fees',
            'actions' => [
                'pay' => [
                    'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                    'value' => 'Pay',
                    'requireRows' => true
                ],
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'selfserve-fees-table-fee-description',
            'name' => 'description',
            'formatter' => FeeUrlExternal::class,
        ],
        [
            'title' => 'selfserve-fees-table-fee-reference',
            'formatter' => fn($row, $col) => $row['licence']['licNo'],
        ],
        [
            'title' => 'selfserve-fees-table-fee-licence-outstanding',
            'name' => 'outstanding',
            'formatter' => FeeAmount::class,
            'isNumeric' => true,
        ],
        [
            'title' => 'action',
            'type' => 'Checkbox',
            'width' => 'checkbox',
            'name' => 'checkbox',
            'disabled-callback' => fn($row) => $row['isExpiredForLicence']
        ]
    ],
    'footer' => [
        'total' => [
            'type' => 'th',
            'content' => 'dashboard-fees-total',
            'formatter' => Translate::class,
            'colspan' => 2
        ],
        [
            'type' => 'th',
            'formatter' => FeeAmountSum::class,
            'name' => 'amount',
            'align' => 'govuk-!-text-align-right',
        ],
        'remainingColspan' => [
            'type' => 'th',
            'colspan' => 1
        ],
    ],
];
