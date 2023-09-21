<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\YesNo;

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'entity-view-label-operating-centre',
            'formatter' => Address::class,
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ),
        array(
            'title' => 'entity-view-label-environmental-complaints',
            'formatter' => YesNo::class,
            'stack' => 'operatingCentre->hasEnvironmentalComplaint'
        ),
        array(
            'title' => 'entity-view-label-oppositions',
            'formatter' => YesNo::class,
            'stack' => 'operatingCentre->hasOpposition'
        )
    )
);
