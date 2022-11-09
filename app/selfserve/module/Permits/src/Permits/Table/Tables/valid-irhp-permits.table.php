<?php

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
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => 'NullableNumber',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.issue-date',
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
            'name' => 'expiryDate',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'useByDate',
                        'formatter' => 'Date',
                    ],
                    [
                        'useByDate' => $row['irhpPermitRange']['irhpPermitStock']['validTo'] ?? null,
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
