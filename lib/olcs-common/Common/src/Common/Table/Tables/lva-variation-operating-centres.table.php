<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\OcComplaints;
use Common\Service\Table\Formatter\OpCentreDeltaSum;
use Common\Service\Table\Formatter\Translate;

return [
    'variables' => [
        'title' => 'application_operating-centres_authorisation.table.title',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'Add operating centre'
                ],
                'schedule41' => [
                    'value' => 'Add schedule 4/1',
                    'requireRows' => false
                ]
            ]
        ],
        'row-disabled-callback' => static fn($row) => in_array($row['action'], ['D', 'C'])
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'application_operating-centres_authorisation.table.address',
            'type' => 'OperatingCentreVariationRecordAction',
            'action' => 'edit',
            'name' => 'operatingCentre->address',
            'formatter' => Address::class,
            'addressFields' => 'BRIEF',
            'sort' => 'adr',
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
        ],
        [
            'title' => 'application_operating-centres_authorisation.table.complaints',
            'isNumeric' => true,
            'name' => 'noOfComplaints',
            'formatter' => OcComplaints::class
        ],
        [
            'title' => 'markup-table-th-remove-restore', //view partial from olcs-common
            'ariaDescription' => static fn($row) => $row['operatingCentre']['address']['addressLine1'],
            'type' => 'DeltaActionLinks'
        ],
    ],
    'footer' => [
        'total' => [
            'type' => 'th',
            'content' => 'application_operating-centres_authorisation.table.footer.total',
            'formatter' => Translate::class,
            'colspan' => 1
        ],
        [
            'formatter' => OpCentreDeltaSum::class,
            'align' => 'govuk-!-text-align-right',
            'name' => 'noOfVehiclesRequired'
        ],
        'trailersCol' => [
            'formatter' => OpCentreDeltaSum::class,
            'align' => 'govuk-!-text-align-right',
            'name' => 'noOfTrailersRequired'
        ],
        'remainingColspan' => [
            'colspan' => 3
        ]
    ]
];
