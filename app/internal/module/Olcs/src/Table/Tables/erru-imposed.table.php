<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'Imposed penalty',
        'title' => 'Imposed penalties'
    ],
    'settings' => [

    ],
    'columns' => [
        [
            'title' => 'Final decision date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'finalDecisionDate'
        ],
        [
            'title' => 'Penalty type',
            'formatter' => fn($data) => $data['siPenaltyImposedType']['id'] . ' - ' . $data['siPenaltyImposedType']['description'],
        ],
        [
            'title' => 'Start date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'startDate'
        ],
        [
            'title' => 'End date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'endDate'
        ],
        [
            'title' => 'Executed',
            'formatter' => fn($data) => $data['executed']['description'],
        ]
    ]
];
