<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'entity-view-label-operating-centre',
            'formatter' => Address::class,
            'addressFields' => 'FULL',
            'name' => 'operatingCentre->address'
        ],
        [
            'title' => 'entity-view-label-environmental-complaints',
            'formatter' => YesNo::class,
            'stack' => 'operatingCentre->hasEnvironmentalComplaint'
        ],
        [
            'title' => 'entity-view-label-oppositions',
            'formatter' => YesNo::class,
            'stack' => 'operatingCentre->hasOpposition'
        ]
    ]
];
