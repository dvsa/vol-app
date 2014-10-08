<?php

return array(
    'variables' => array(
        'title' => 'Convictions list'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'useQuery' => true
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
            'name' => 'description'
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
