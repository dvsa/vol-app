<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\ConditionsUndertakingsType;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Conditions & Undertakings'
    ],
    'columns' => [
        [
            'title' => 'No.',
            'name' => 'id'
        ],
        [
            'title' => 'lva-conditions-undertakings-table-type',
            'formatter' => ConditionsUndertakingsType::class,
        ],
        [
            'title' => 'Added via',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->translator->translate($data['addedVia']['description']),
        ],
        [
            'title' => 'Fulfilled',
            'formatter' => fn($data, $column) => $data['isFulfilled'] == 'Y' ? 'Yes' : 'No',
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => $data['isDraft'] == 'Y' ? 'Draft' : 'Approved',
        ],
        [
            'title' => 'Attached to',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->translator->translate($data['attachedTo']['description']),
        ],
        [
            'title' => 'OC address',
            'width' => '300px',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                if (isset($data['operatingCentre']['address'])) {
                    $column['formatter'] = Address::class;

                    return $this->callFormatter($column, $data['operatingCentre']['address']);
                }

                return 'N/a';
            }
        ],
    ]
];
