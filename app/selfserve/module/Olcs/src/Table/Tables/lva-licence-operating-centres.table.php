<?php

return array(
    'variables' => array(
        'title' => 'application_operating-centres_authorisation.table.title',
        'empty_message' => 'application_operating-centres_authorisation-tableEmptyMessage',
        'within_form' => true,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'application_operating-centres_authorisation.table.address',
            'name' => 'operatingCentre->address',
            'formatter' => 'Address',
            'addressFields' => 'BRIEF',
            'sort' => 'adr'
        ),
        array(
            'title' => 'application_operating-centres_authorisation.table.vehicles',
            'name' => 'noOfVehiclesRequired',
            'sort' => 'noOfVehiclesRequired'
        ),
        array(
            'title' => 'application_operating-centres_authorisation.table.trailers',
            'name' => 'noOfTrailersRequired',
            'sort' => 'noOfTrailersRequired'
        )
    ),
    'footer' => array(
        'total' => array(
            'type' => 'th',
            'content' => 'application_operating-centres_authorisation.table.footer.total',
            'formatter' => 'Translate',
            'colspan' => 1
        ),
        array(
            'formatter' => 'Sum',
            'name' => 'noOfVehiclesRequired'
        ),
        'trailersCol' => array(
            'formatter' => 'Sum',
            'name' => 'noOfTrailersRequired'
        )
    )
);
