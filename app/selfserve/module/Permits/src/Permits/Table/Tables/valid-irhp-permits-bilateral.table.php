<?php

use Common\Util\Escape;
use Common\RefData;

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
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'isNumeric' => true,
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => 'NullableNumber',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.country',
            'name' => 'country',
            'formatter' => function ($row, $column, $sm) {
                $translator = $sm->get('translator');
                return Escape::html(
                    $translator->translate($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc'])
                );
            },
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.type',
            'name' => 'type',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'irhpPermitRangeType',
                        'formatter' => 'IrhpPermitRangeType',
                    ],
                    $row['irhpPermitRange']
                );
            }
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.issued-date',
            'name' => 'issueDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'startDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'ceasedDate',
            'formatter' => 'Date',
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
