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
            'title' => 'permits.irhp.fee-breakdown.year',
            'isNumeric' => true,
            'name' => 'year',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.validity-period',
            'name' => 'validityPeriod',
            'formatter' => 'ValidityPeriod',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.fee-per-permit',
            'isNumeric' => true,
            'name' => 'feePerPermit',
            'formatter' => function ($row, $column) {
                return (new CurrencyFormatter())($row['feePerPermit']);
            }
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.number-of-permits',
            'isNumeric' => true,
            'name' => 'numberOfPermits',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.total-fee',
            'isNumeric' => true,
            'name' => 'totalFee',
            'formatter' => function ($row, $column) {
                return (new CurrencyFormatter())($row['totalFee']);
            }
        ),
    )
);
