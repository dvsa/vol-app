<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\InterimOcCheckbox;

return [
    'variables' => [
        'title' => 'internal.interim.operatingcentres.table.header',
        'within_form' => true
    ],
    'settings' => [
        'within_form' => true,
        'crud' => [
            'actions' => [],
            'formName' => 'operatingCentres'
        ],
    ],
    'columns' => [
        [
            'title' => 'internal.interim.operatingcentres.table.address',
            'name' => 'operatingCentre->address',
            'formatter' => Address::class
        ],
        [
            'title' => 'internal.interim.operatingcentres.table.vehicles',
            'name' => 'noOfVehiclesRequired',
        ],
        [
            'title' => 'internal.interim.operatingcentres.table.trailers',
            'name' => 'noOfTrailersRequired',
        ],
        [
            'title' => 'internal.interim.operatingcentres.table.listed',
            'width' => 'checkbox',
            'formatter' => InterimOcCheckbox::class,
            'name' => 'listed'
        ],
    ]
];
