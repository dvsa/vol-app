<?php

return array(
    'settings' => array(
        'submission_section' => 'display'
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Date of conviction',
            'formatter' => function ($data, $column) {

                if ($data['convictionDate'] == null) {
                    return 'N/A';
                }

                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'convictionDate'
        ),
        array(
            'title' => 'Date of offence',
            'formatter' => 'Date',
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Name / defendant type',
            'name' => 'name'
        ),
        array(
            'title' => 'Description',
            'name' => 'categoryText'
        ),
        array(
            'title' => 'Court/FPN',
            'name' => 'court'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty'
        ),
        array(
            'title' => 'SI',
            'name' => 'msi'
        ),
        array(
            'title' => 'Declared',
            'name' => 'isDeclared'
        ),
        array(
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
        )
    )
);
