<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'entity-view-label-operating-centre',
            'addressFields' => 'FULL',
            'formatter' => Address::class,
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'entity-view-table-header-interim',
            'formatter' => YesNo::class,
            'name' => 'isInterim'
        ),
        array(
            'title' => 'entity-view-table-header-vehicles-authorised',
            'isNumeric' => true,
            'formatter' => function ($data) {
                return !empty($data['noOfVehiclesRequired']) ?
                    $data['noOfVehiclesRequired'] : '0';
            },
            'name' => 'noOfVehiclesRequired'
        ),
        array(
            'title' => 'entity-view-table-header-trailers-authorised',
            'isNumeric' => true,
            'formatter' => function ($data) {
                return !empty($data['noOfTrailersRequired']) ?
                    $data['noOfTrailersRequired'] : '0';
            }
        ),
        array(
            'title' => 'entity-view-table-header-date-added',
            'name' => 'createdOn',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $row['operatingCentre']);
            }
        ),
        array(
            'title' => 'entity-view-table-header-date-removed',
            'name' => 'deletedDate',
            'formatter' => function ($row, $column) {
                $column['formatter'] = Date::class;
                if (empty($row['deletedDate'])) {
                    return 'NA';
                }
                return $this->callFormatter($column, $row['deletedDate']);
            }
        )
    )
);
