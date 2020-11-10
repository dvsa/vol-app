<?php

use Common\Util\Escape;

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(
        // TODO: tidy up when more time available
        'style' => 'margin-bottom: 20px'
    ),
    'columns' => array(
        array(
            'title' => 'permits.irhp.unpaid.permits.table.permit',
            'name' => 'permitNumber',
            'formatter' => function ($row) {
                return '<b>' . Escape::html($row['permitNumber']) . '</b>';
            },
        ),
        array(
            'title' => 'permits.irhp.unpaid.permits.table.min-emission',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.irhp.unpaid.permits.table.countries',
            'name' => 'constrainedCountries',
            'formatter' => 'ConstrainedCountriesList',
        ),
    )
);
