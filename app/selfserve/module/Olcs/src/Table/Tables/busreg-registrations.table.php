<?php

return array(
    'variables' => array(
        'title' => 'Bus registrations'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Organisation',
            'name' => 'organisationName'
        ),
        array(
            'title' => 'Bus registration No.',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrRegNumberLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'Variation No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
            'formatter' => function ($row) {
                return str_replace('(', ' (', $row['serviceNo']);
            }
        ),
        array(
            'title' => '1st registered / cancelled',
            'name' => 'date1stReg',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
        ),
        array(
            'title' => 'Starting point',
            'name' => 'startPoint'
        ),
        array(
            'title' => 'Finishing point',
            'name' => 'finishPoint'
        )
    )
);
