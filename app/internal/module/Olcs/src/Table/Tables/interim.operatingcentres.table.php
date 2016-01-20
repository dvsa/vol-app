<?php

return array(
    'variables' => array(
        'title' => 'internal.interim.operatingcentres.table.header',
        'within_form' => true
    ),
    'settings' => array(
        'within_form' => true,
        'crud' => array(
            'actions' => array(),
            'formName' => 'operatingCentres'
        ),
    ),
    'columns' => array(
        array(
            'title' => 'internal.interim.operatingcentres.table.address',
            'name' => 'operatingCentre->address',
            'formatter' => 'Address'
        ),
        array(
            'title' => 'internal.interim.operatingcentres.table.vehicles',
            'name' => 'noOfVehiclesRequired',
        ),
        array(
            'title' => 'internal.interim.operatingcentres.table.trailers',
            'name' => 'noOfTrailersRequired',
        ),
        array(
            'title' => 'internal.interim.operatingcentres.table.listed',
            'width' => 'checkbox',
            'formatter' => 'InterimOcCheckbox',
            'name' => 'listed'
        ),
    )
);
