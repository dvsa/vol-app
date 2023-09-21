<?php

use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'titleSingular' => 'Imposed penalty',
        'title' => 'Imposed penalties'
    ),
    'settings' => array(

    ),
    'columns' => array(
        array(
            'title' => 'Final decision date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'finalDecisionDate'
        ),
        array(
            'title' => 'Penalty type',
            'formatter' => function ($data) {
                return $data['siPenaltyImposedType']['id'] . ' - ' . $data['siPenaltyImposedType']['description'];
            },
        ),
        array(
            'title' => 'Start date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'startDate'
        ),
        array(
            'title' => 'End date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'endDate'
        ),
        array(
            'title' => 'Executed',
            'formatter' => function ($data) {
                return $data['executed']['description'];
            },
        )
    )
);
