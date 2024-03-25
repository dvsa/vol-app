<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\StackValue;
use Common\Util\Escape;

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [
        'style' => 'margin-bottom: 20px'
    ],
    'columns' => [
        [
            'title' => 'permits.irhp.unpaid.permits.table.permit',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => fn($row) => '<b>' . Escape::html($row['permitNumber']) . '</b>',
        ],
        [
            'title' => 'permits.irhp.unpaid.permits.table.min-emission',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'permits.irhp.unpaid.permits.table.countries',
            'name' => 'constrainedCountries',
            'formatter' => ConstrainedCountriesList::class,
        ],
    ]
];
