<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Sum;
use Common\Service\Table\Formatter\Translate;

return [
    'variables' => [
        'title' => 'application_operating-centres_authorisation.table.title',
        'empty_message' => 'application_operating-centres_authorisation-tableEmptyMessage',
        'within_form' => true,
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'application_operating-centres_authorisation.table.address',
            'name' => 'operatingCentre->address',
            'formatter' => Address::class,
            'addressFields' => 'BRIEF',
            'sort' => 'adr'
        ],
        [
            'title' => 'application_operating-centres_authorisation.table.vehicles',
            'isNumeric' => true,
            'name' => 'noOfVehiclesRequired',
            'sort' => 'noOfVehiclesRequired'
        ],
        [
            'title' => 'application_operating-centres_authorisation.table.trailers',
            'isNumeric' => true,
            'name' => 'noOfTrailersRequired',
            'sort' => 'noOfTrailersRequired'
        ]
    ],
    'footer' => [
        'total' => [
            'type' => 'th',
            'content' => 'application_operating-centres_authorisation.table.footer.total',
            'formatter' => Translate::class,
            'colspan' => 1
        ],
        [
            'formatter' => Sum::class,
            'align' => 'govuk-!-text-align-right',
            'name' => 'noOfVehiclesRequired'
        ],
        'trailersCol' => [
            'formatter' => Sum::class,
            'align' => 'govuk-!-text-align-right',
            'name' => 'noOfTrailersRequired'
        ]
    ]
];
