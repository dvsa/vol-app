<?php

use Common\Util\Escape;

/* note: this is the same table as unpaid-irhp-permits aside from having an additional column for the checkbox
 * placeholder and no pagination. It should be kept in sync with unpaid-irhp-permits.
 */

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array('class' => 'candidate-permit-selection'),
    'columns' => array(
        array(
            'title' => 'permits.irhp.unpaid.permits.table.permit',
            'isNumeric' => true,
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
        array(
            'title' => 'permits.irhp.unpaid.permits.deselect-unwanted',
            'name' => 'deselectUnwanted',
            'formatter' => function ($row) {
                return '{checkboxPlaceholder}';
            }
        ),
    )
);
