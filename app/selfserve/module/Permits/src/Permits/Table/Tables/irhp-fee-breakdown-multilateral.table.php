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
            'name' => 'year',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.validity-period',
            'name' => 'validityPeriod',
            'formatter' => 'ValidityPeriod',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.fee-per-permit',
            'name' => 'feePerPermit',
            'formatter' => function ($row, $column, $sm) {
                return (new CurrencyFormatter())($row['feePerPermit']);
            }
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.number-of-permits',
            'name' => 'numberOfPermits',
        ),
        array(
            'title' => 'permits.irhp.fee-breakdown.total-fee',
            'name' => 'totalFee',
            'formatter' => function ($row, $column, $sm) {
                return (new CurrencyFormatter())($row['totalFee']);
            }
        ),
    )
);
