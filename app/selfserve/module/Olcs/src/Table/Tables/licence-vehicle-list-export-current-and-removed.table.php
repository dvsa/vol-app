<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValueReplacer;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Common\Service\Table\Formatter\VehicleRegistrationMark;

return [
    'columns' => [
        [
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.vrm.title',
            'formatter' => VehicleRegistrationMark::class,
            'action' => 'edit',
            'type' => 'Action',
            'sort' => 'v.vrm'
        ],
        [
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.plated-weight.title',
            'isNumeric' => true,
            'stringFormat' => '{vehicle->platedWeight} kg',
            'formatter' => StackValueReplacer::class
        ],
        [
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.specified-date.title',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ],
        [
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.removal-date.title',
            'formatter' => Date::class,
            'name' => 'removalDate',
            'sort' => 'removalDate'
        ],
        [
            'title' => 'table.licence-vehicle-list-export-current-and-removed.column.disc-number.title',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => VehicleDiscNo::class
        ],
    ]
];
