<?php

use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReference;
use Common\Service\Table\Formatter\RefData;
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
                    'class' => 'govuk-button'
                ],
                'cancel' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary'
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
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
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
            'title' => 'Status',
            'name' => 'status',
            'formatter' => RefData::class,
        ],
    ]
];
