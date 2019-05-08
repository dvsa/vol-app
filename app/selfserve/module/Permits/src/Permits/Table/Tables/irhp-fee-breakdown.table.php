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
            'formatter' => function ($row, $column, $sm) {
                $dateFormatter = $sm->get('ViewHelperManager')->get('DateFormat');
                $translator = $sm->get('Translator');

                $validFrom = $dateFormatter(
                    date($row['validFromTimestamp']),
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::NONE,
                    $translator->getLocale()
                );
                $validFrom = trim(str_replace($row['year'], '', $validFrom));

                $validTo = $dateFormatter(
                    date($row['validToTimestamp']),
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::NONE,
                    $translator->getLocale()
                );
                $validTo = trim(str_replace($row['year'], '', $validTo));

                return sprintf(
                    $translator->translate('permits.irhp.fee-breakdown.validity-period.cell'),
                    $validFrom,
                    $validTo
                );
            }
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
