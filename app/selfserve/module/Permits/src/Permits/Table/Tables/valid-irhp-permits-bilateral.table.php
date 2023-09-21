<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\StackValue;
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
            'formatter' => StackValue::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.permit-no',
            'isNumeric' => true,
            'name' => 'permitNumber',
            'formatter' => NullableNumber::class,
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.country',
            'name' => 'country',
            'formatter' => function ($row, $column) {
                return Escape::html(
                    $this->translator->translate($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc'])
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
                        'formatter' => IrhpPermitRangeType::class,
                    ],
                    $row['irhpPermitRange']
                );
            }
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.issued-date',
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
            'name' => 'ceasedDate',
            'formatter' => Date::class,
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
