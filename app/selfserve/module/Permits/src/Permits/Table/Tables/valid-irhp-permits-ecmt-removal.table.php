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
            'name' => 'permitNumber',
            'formatter' => function ($row) {
                return '<b>' . Escape::html($row['permitNumber']) . '</b>';
            },
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.countries',
            'name' => 'countries',
            'formatter' => function ($row) {
                return 'Cyprus';
            }
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'issueDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'expiryDate',
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
