<?php

use Common\Util\Escape;
use Common\View\Helper\CurrencyFormatter;

return array(
    'variables' => array(),
    'settings' => array(
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'permits.irhp.fee-breakdown.country',
            'name' => 'countryName',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.type',
            'name' => 'type',
            'formatter' => function ($row, $column) {
                return Escape::html(
                    $this->translator->translate($row['type'])
                );
            },
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.number-of-permits',
            'isNumeric' => true,
            'name' => 'quantity',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.total-fee',
            'isNumeric' => true,
            'name' => 'total',
            'formatter' => function ($row, $column) {
                return (new CurrencyFormatter())($row['total']);
            }
        ),
    ),
    'footer' => array(
        array(
            'content' => 'Total',
            'colspan' => 2,
            'align' => 'govuk-!-text-align-left',
        ),
        array(
            'align' => 'govuk-!-text-align-left',
            'formatter' => function ($rows, $column) {
                $total = 0;
                foreach ($rows as $row) {
                    $total += $row['quantity'];
                }
                return $total;
            }
        ),
        array(
            'content' => 'Total',
            'isNumeric' => true,
            'formatter' => function ($rows, $column) {
                $total = 0;
                foreach ($rows as $row) {
                    $total += $row['total'];
                }

                return (new CurrencyFormatter())($total);
            }
        ),
    )
);
