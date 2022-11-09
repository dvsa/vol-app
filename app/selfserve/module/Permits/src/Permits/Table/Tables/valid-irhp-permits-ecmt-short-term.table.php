<?php

use Common\RefData;
use Common\Util\Escape;

return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            ),
        ),
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => function ($row) {
                return '<b>' . Escape::html($row['permitNumber']) . '</b>';
            },
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'isNumeric' => true,
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.emissions-standard',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.constrained.countries',
            'name' => 'constrainedCountries',
            'formatter' => 'ConstrainedCountriesList',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.issue-date',
            'name' => 'issueDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.use-by-date',
            'name' => 'useByDate',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'useByDate',
                        'formatter' => 'Date',
                    ],
                    [
                        'useByDate' => $row['ceasedDate'],
                    ]
                );
            }
        ),
        array(
            'title' => 'status',
            'name' => 'status',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'status',
                        'formatter' => 'RefDataStatus',
                    ],
                    [
                        'status' => [
                            'id' => RefData::PERMIT_VALID,
                            'description' => RefData::PERMIT_VALID
                        ],
                    ]
                );
            }
        ),
    )
);
