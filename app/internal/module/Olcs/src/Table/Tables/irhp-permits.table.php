<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\IrhpPermitNumberInternal;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
        'id' => 'permits-table',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'terminate' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'request replacement' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one']
            ],
        ],
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
            'formatter' => IrhpPermitNumberInternal::class,
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
            'title' => 'Issued date',
            'name' => 'issueDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'Type',
            'name' => 'type',
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
                ),
        ],
        [
            'title' => 'Country',
            'name' => 'country',
            'formatter' => fn($row) => Escape::html($row['irhpPermitRange']['irhpPermitStock']['country']['countryDesc']),
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
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ],
];
