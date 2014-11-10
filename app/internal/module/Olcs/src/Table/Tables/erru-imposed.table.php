<?php

return array(
    'variables' => array(
        'title' => 'Imposed penalties'
    ),
    'settings' => array(

    ),
    'columns' => array(
        array(
            'title' => 'Final decision date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'finalDecisionDate'
        ),
        array(
            'title' => 'Penalty type',
            'formatter' => function ($data) {
                return $data['siPenaltyImposedType']['description'];
            },
        ),
        array(
            'title' => 'Start date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'startDate'
        ),
        array(
            'title' => 'End date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'endDate'
        ),
        array(
            'title' => 'Executed',
            'formatter' => function ($data) {
                return $data['executed'] == 'Y' ? 'Yes' : 'No';
            },
        )
    )
);
