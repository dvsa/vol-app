<?php

return array(
    'variables' => array(
        'title' => 'Convictions list'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date of conviction',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'conviction', true);

                if ($data['dateOfConviction'] == null) {
                    return '<a href="' . $url . '">N/A</a>';
                }

                $column['formatter'] = 'Date';
                return '<a href="' . $url . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'dateOfConviction'
        ),
        array(
            'title' => 'Date of offence',
            'formatter' => 'Date',
            'name' => 'dateOfOffence'
        ),
        array(
            'title' => 'Name / defendant type',
            'formatter' => function ($data) {
                $person = $data['personFirstname'] . ' ' . $data['personLastname'];
                $organisationName = $data['operatorName'];
                return ($organisationName == '' ? $person : $organisationName) . ' / ' . $data['defType'];
            }
        ),
        array(
            'title' => 'Description',
            'name' => 'categoryText'
        ),
        array(
            'title' => 'Court/FPN',
            'name' => 'courtFpm'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty'
        ),
        array(
            'title' => 'SI',
            'name' => 'si'
        ),
        array(
            'title' => 'Declared',
            'name' => 'decToTc'
        ),
        array(
            'title' => 'Dealt with',
            'name' => 'dealtWith'
        )
    )
);
