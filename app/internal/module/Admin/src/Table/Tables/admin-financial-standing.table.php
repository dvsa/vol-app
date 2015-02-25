<?php

return array(
    'variables' => array(
        'title' => 'Financial standing rates'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'requireRows' => false),
                'edit' => array('class' => 'secondary', 'requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
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
