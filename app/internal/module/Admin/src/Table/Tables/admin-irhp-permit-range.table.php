<?php

use Common\Service\Table\Formatter\IrhpPermitRangePermitNumber;
use Common\Service\Table\Formatter\IrhpPermitRangeReplacement;
use Common\Service\Table\Formatter\IrhpPermitRangeReserve;
use Common\Service\Table\Formatter\IrhpPermitRangeRestrictedCountries;
use Common\Service\Table\Formatter\IrhpPermitRangeTotalPermits;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'title' => 'Permit Ranges',
        'titleSingular' => 'Permit Range',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Permit numbers',
            'name' => 'permitNumbers',
            'formatter' => IrhpPermitRangePermitNumber::class
        ],
        [
            'title' => 'Emissions Question',
            'name' => 'emissionsCategory',
            'formatter' => RefData::class
        ],
        [
            'title' => 'Type',
            'name' => 'permitRangeType',
            'formatter' => IrhpPermitRangeType::class
        ],
        [
            'title' => 'Restricted countries',
            'name' => 'restrictedCountries',
            'formatter' => IrhpPermitRangeRestrictedCountries::class
        ],
        [
            'title' => 'Minister of state reserve',
            'name' => 'ssReserve',
            'formatter' => IrhpPermitRangeReserve::class
        ],
        [
            'title' => 'Replacement stock',
            'name' => 'lostReplacement',
            'formatter' => IrhpPermitRangeReplacement::class
        ],
        [
            'title' => 'Total permits',
            'isNumeric' => true,
            'name' => 'totalPermits',
            'formatter' => IrhpPermitRangeTotalPermits::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
