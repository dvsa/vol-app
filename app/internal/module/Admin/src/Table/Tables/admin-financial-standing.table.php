<?php

return array(
    'variables' => array(
        'title' => 'crud-financial-standing-title'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('class' => 'action--secondary js-require--one'),
                'delete' => array('class' => 'action--secondary js-require--multiple')
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
            'title' => 'First',
            'name' => 'firstVehicleRate',
        ),
        array(
            'title' => 'Additional',
            'name' => 'additionalVehicleRate',
        ),
        array(
            'title' => 'Effective',
            'name' => 'effectiveFrom',
            'formatter' => 'Date'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    )
);
