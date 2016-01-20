<?php

return array(
    'variables' => array(
        'title' => 'internal.interim.vehicles.table.header',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(),
            'formName' => 'vehicles'
        ),
    ),
    'columns' => array(
        array(
            'title' => 'internal.interim.vehicles.table.vrm',
            'name' => 'vrm',
            'formatter' => function ($data) {
                return $data['vehicle']['vrm'];
            },
        ),
        array(
            'title' => 'internal.interim.vehicles.table.weight',
            'name' => 'platedWeight',
            'formatter' => function ($data) {
                return $data['vehicle']['platedWeight'];
            },
        ),
        array(
            'title' => 'internal.interim.vehicles.table.listed',
            'width' => 'checkbox',
            'formatter' => 'InterimVehiclesCheckbox',
            'name' => 'listed'
        ),
    )
);
