<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Printer must be loaded with the templates for the following Permit Numbers and in this order.',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'print' => [
                    'requireRows' => false,
                    'label' => 'Confirm',
                    'class' => 'action--primary'
                ],
                'cancel' => [
                    'requireRows' => false,
                    'class' => 'action--secondary'
                ]
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Sequence Number',
            'isNumeric' => true,
            'name' => 'sequenceNumber'
        ],
        [
            'title' => 'Permit No',
            'isNumeric' => true,
            'name' => 'permitNumberWithPrefix'
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
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => 'DateTime',
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
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'RefData',
        ],
    ]
];
