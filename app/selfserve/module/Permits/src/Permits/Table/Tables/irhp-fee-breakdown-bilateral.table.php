<?php

use Common\Util\Escape;
use Common\View\Helper\CurrencyFormatter;

return [
    'variables' => [],
    'settings' => [
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'permits.irhp.fee-breakdown.country',
            'name' => 'countryName',
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.type',
            'name' => 'type',
            'formatter' => fn($row, $column) => Escape::html(
                $this->translator->translate($row['type'])
            ),
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.number-of-permits',
            'isNumeric' => true,
            'name' => 'quantity',
        ],
        [
            'title' => 'permits.irhp.fee-breakdown.total-fee',
            'isNumeric' => true,
            'name' => 'total',
            'formatter' => fn($row, $column) => (new CurrencyFormatter())($row['total'])
        ],
    ],
    'footer' => [
        [
            'content' => 'Total',
            'colspan' => 2,
            'align' => 'govuk-!-text-align-left',
        ],
        [
            'align' => 'govuk-!-text-align-left',
            'formatter' => function ($rows, $column) {
                $total = 0;
                foreach ($rows as $row) {
                    $total += $row['quantity'];
                }
                return $total;
            }
        ],
        [
            'content' => 'Total',
            'isNumeric' => true,
            'formatter' => function ($rows, $column) {
                $total = 0;
                foreach ($rows as $row) {
                    $total += $row['total'];
                }

                return (new CurrencyFormatter())($total);
            }
        ],
    ]
];
