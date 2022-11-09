<?php

return [
    'columns' => array(
        array(
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.vrm.title',
            'formatter' => 'VehicleRegistrationMark',
            'action' => 'edit',
            'type' => 'Action',
            'sort' => 'v.vrm'
        ),
        array(
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.plated-weight.title',
            'isNumeric' => true,
            'stringFormat' => '{vehicle->platedWeight} kg',
            'formatter' => 'StackValueReplacer'
        ),
        array(
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.specified-date.title',
            'formatter' => 'Date',
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ),
        array(
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.removal-date.title',
            'formatter' => 'Date',
            'name' => 'removalDate',
            'sort' => 'removalDate'
        ),
        array(
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.disc-number.title',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => 'VehicleDiscNo'
        ),
    )
];
