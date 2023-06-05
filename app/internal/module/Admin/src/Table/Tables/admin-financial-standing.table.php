<?php

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
            'formatter' => 'RefData'
        ),
        array(
            'title' => 'Licence type',
            'name' => 'licenceType',
            'formatter' => 'RefData'
        ),
        array(
            'title' => 'Vehicle type',
            'name' => 'vehicleType',
            'formatter' => 'RefData',
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
            'formatter' => 'Date'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    )
);
