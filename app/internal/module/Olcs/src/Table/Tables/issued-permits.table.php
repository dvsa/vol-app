<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReference;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
        'id' => 'permits-table',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Permit No.',
            'isNumeric' => true,
            'name' => 'permitNumber',
        ],
        [
            'title' => 'Application number',
            'isNumeric' => true,
            'name' => 'id',
            'formatter' => function ($row) {
                $relatedApplication = $row['irhpPermitApplication']['relatedApplication'];

                return $this->callFormatter(
                    [
                        'name' => 'id',
                        'formatter' => IssuedPermitLicencePermitReference::class,
                    ],
                    [
                        'id' => $relatedApplication['id'],
                        'typeId' => $row['irhpPermitRange']['irhpPermitStock']['irhpPermitType']['id'],
                        'licenceId' => $relatedApplication['licence']['id'],
                        'applicationRef' => $relatedApplication['id'],
                    ]
                );
            }
        ],
        [
            'title' => 'Type',
            'name' => 'type',
            'formatter' => fn($row) => Escape::html($row['irhpPermitRange']['irhpPermitStock']['irhpPermitType']['name']['description']),
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'formatter' => fn($row) => Escape::html($row['irhpPermitRange']['emissionsCategory']['description']),
        ],
        [
            'title' => 'Not valid to travel to',
            'name' => 'constrainedCountries',
            'formatter' => ConstrainedCountriesList::class,
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => fn($row) => Escape::html($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc']),
        ],
        [
            'title' => 'Usage',
            'name' => 'usage',
            'formatter' => fn($row) => $this->callFormatter(
                [
                    'name' => 'irhpPermitRangeType',
                    'formatter' => IrhpPermitRangeType::class,
                ],
                $row['irhpPermitRange']
            )
        ],
        [
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'Ceased Date',
            'name' => 'ceasedDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'Replacement',
            'name' => 'replaces',
            'formatter' => function ($row) {
                $val = is_array($row['replaces']) ? 'Yes' : 'No';
                return $val;
            },
        ],
        [
            'title' => 'Status',
            'name' => 'status',
            'formatter' => RefDataStatus::class
        ],
    ],
];
