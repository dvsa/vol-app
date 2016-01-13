<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'entity-view-label-operating-centre',
            'formatter' => 'Address',
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'entity-view-label-environmental-complaints',
            'formatter' => 'YesNo',
            'stack' => 'operatingCentre->hasEnvironmentalComplaint'
        ),
        array(
            'title' => 'entity-view-label-oppositions',
            'formatter' => 'YesNo',
            'stack' => 'operatingCentre->hasOpposition'
        )
    )
);
