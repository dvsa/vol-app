<?php

use Common\RefData;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;

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
            'formatter' => NullableNumber::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.application-no',
            'isNumeric' => true,
            'name' => 'irhpApplication',
            'stack' => 'irhpPermitApplication->relatedApplication->id',
            'formatter' => StackValue::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.issue-date',
            'name' => 'issueDate',
            'formatter' => Date::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'startDate',
            'formatter' => Date::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'expiryDate',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'expiryDate',
                        'formatter' => Date::class,
                    ],
                    [
                        'expiryDate' => $row['irhpPermitRange']['irhpPermitStock']['validTo'] ?? null,
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
                        'formatter' => RefDataStatus::class,
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
