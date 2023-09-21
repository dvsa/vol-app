<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return array(
    'variables' => array(
        'title' => 'crud-financial-standing-title'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('class' => 'govuk-button govuk-button--warning js-require--multiple')
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Operator type',
            'type' => 'Action',
            'action' => 'edit',
            'name' => 'goodsOrPsv',
            'formatter' => RefData::class
        ),
        array(
            'title' => 'Licence type',
            'name' => 'licenceType',
            'formatter' => RefData::class
        ),
        array(
            'title' => 'Vehicle type',
            'name' => 'vehicleType',
            'formatter' => RefData::class
        ),
        array(
            'title' => 'First',
            'isNumeric' => true,
            'name' => 'firstVehicleRate',
        ),
        array(
            'title' => 'Additional',
            'isNumeric' => true,
            'name' => 'additionalVehicleRate',
        ),
        array(
            'title' => 'Effective',
            'name' => 'effectiveFrom',
            'formatter' => Date::class
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    )
);
