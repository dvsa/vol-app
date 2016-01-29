<?php

return array(
    'variables' => array(
        'title' => 'schedule41.operating-centre.table.title',
        'empty_message' => 'schedule41.operating-centre.table.empty',
        'within_form' => true,
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array()
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'schedule41.operating-centre.table.address',
            'formatter' => 'Address',
            'name' => 'address'
        ),
        array(
            'title' => 'schedule41.operating-centre.table.vehicles',
            'name' => 'noOfVehiclesRequired'
        ),
        array(
            'title' => 'schedule41.operating-centre.table.trailers',
            'name' => 'noOfTrailersRequired'
        ),
        array(
            'title' => 'schedule41.operating-centre.table.conditions',
            'name' => 'noOfConditions',
            'formatter' => 'OcConditions'
        ),
        array(
            'title' => 'schedule41.operating-centre.table.undertakings',
            'name' => 'noOfUndertakings',
            'formatter' => 'OcUndertakings'
        ),
        array(
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
