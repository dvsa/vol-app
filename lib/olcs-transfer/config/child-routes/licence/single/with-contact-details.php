<?php

use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'with-contact-details' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'with-contact-details[/]'
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Dvsa\Olcs\Transfer\Query\Licence\LicenceWithCorrespondenceCd::class),
        ]
    ],
];
