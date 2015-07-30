<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'entity-view-label-operating-centre',
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
            'name' => 'noOfVehiclesPossessed'
        ),
        array(
            'title' => 'entity-view-table-header-trailers-authorised',
            'name' => 'noOfTrailersPossessed'
        ),
        array(
            'title' => 'entity-view-table-header-removed',
            'formatter' => function ($data) {
                return !empty($data['removedDate']) ?
                    'Yes' : 'No';
            }
        ),
        array(
            'title' => 'entity-view-table-header-date-added',
            'formatter' => 'Date',
            'name' => 'createdOn'
        ),
        array(
            'title' => 'entity-view-table-header-date-removed',
            'formatter' => 'Date',
            'name' => 'deletedDate'
        )
    )
);
