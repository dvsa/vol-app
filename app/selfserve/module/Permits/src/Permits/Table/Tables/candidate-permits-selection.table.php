<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\StackValue;
use Common\Util\Escape;

/* note: this is the same table as unpaid-irhp-permits aside from having an additional column for the checkbox
 * placeholder and no pagination. It should be kept in sync with unpaid-irhp-permits.
 */

return [
    'variables' => [],
    'settings' => [],
    'attributes' => ['class' => 'candidate-permit-selection'],
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
        [
            'title' => 'permits.irhp.unpaid.permits.deselect-unwanted',
            'name' => 'deselectUnwanted',
            'formatter' => fn($row) => '{checkboxPlaceholder}'
        ],
    ]
];
