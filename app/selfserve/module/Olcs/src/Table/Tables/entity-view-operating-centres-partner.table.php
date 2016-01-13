<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'entity-view-label-operating-centre',
            'addressFields' => 'FULL',
            'formatter' => 'Address',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'entity-view-table-header-interim',
            'formatter' => 'yesno',
            'name' => 'isInterim'
        ),
        array(
            'title' => 'entity-view-table-header-vehicles-authorised',
            'formatter' => function ($data) {
                return !empty($data['noOfVehiclesRequired']) ?
                    $data['noOfVehiclesRequired'] : '0';
            }
        ),
        array(
            'title' => 'entity-view-table-header-trailers-authorised',
            'formatter' => function ($data) {
                return !empty($data['noOfTrailersRequired']) ?
                    $data['noOfTrailersRequired'] : '0';
            }
        ),
        array(
            'title' => 'entity-view-table-header-date-added',
            'name' => 'createdOn',
            'formatter' => function ($row, $column, $sl) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $row['operatingCentre']);
            }
        ),
        array(
            'title' => 'entity-view-table-header-date-removed',
            'name' => 'deletedDate',
            'formatter' => function ($row, $column, $sl) {
                $column['formatter'] = 'Date';
                if (empty($row['deletedDate'])) {
                    return 'NA';
                }
                return $this->callFormatter($column, $row['deletedDate']);
            }
        )
    )
);
