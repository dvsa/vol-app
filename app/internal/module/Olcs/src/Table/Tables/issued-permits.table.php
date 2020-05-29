<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Permits',
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
            'name' => 'permitNumber',
        ],
        [
            'title' => 'Application number',
            'name' => 'id',
            'formatter' => function ($row) {
                $relatedApplication = $row['irhpPermitApplication']['relatedApplication'];

                return $this->callFormatter(
                    [
                        'name' => 'id',
                        'formatter' => 'IssuedPermitLicencePermitReference',
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
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitRange']['irhpPermitStock']['irhpPermitType']['name']['description']);
            },
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitRange']['emissionsCategory']['description']);
            },
        ],
        [
            'title' => 'Not valid to travel to',
            'name' => 'constrainedCountries',
            'formatter' => 'ConstrainedCountriesList',
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc']);
            },
        ],
        [
            'title' => 'Usage',
            'name' => 'usage',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'irhpPermitRangeType',
                        'formatter' => 'IrhpPermitRangeType',
                    ],
                    $row['irhpPermitRange']
                );
            }
        ],
        [
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => 'DateTime',
        ],
        [
            'title' => 'Ceased Date',
            'name' => 'ceasedDate',
            'formatter' => 'DateTime',
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
            'formatter' => 'RefDataStatus'
        ],
    ],
];
