<?php

use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReference;
use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'confirm' => [
                    'requireRows' => true,
                    'label' => 'Continue',
                    'class' => 'govuk-button js-require--multiple'
                ],
                'cancel' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ],
        ],
        'row-disabled-callback' => fn($row) => in_array(
            $row['status']['id'],
            [
                Common\RefData::IRHP_PERMIT_STATUS_AWAITING_PRINTING,
                Common\RefData::IRHP_PERMIT_STATUS_PRINTING,
            ]
        ),
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Permit No',
            'isNumeric' => true,
            'name' => 'permitNumberWithPrefix',
            'sort' => 'permitNumber',
        ],
        [
            'title' => 'Application number',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'ia.id',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
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
            'sort' => 'issueDate',
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
            'formatter' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->callFormatter(
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
        [
            'type' => 'Checkbox',
            'width' => 'checkbox',
            'disableIfRowIsDisabled' => true,
        ],
    ]
];
