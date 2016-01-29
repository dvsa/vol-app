<?php

return array(
    'variables' => array(
        'title' => 'Licences'
    ),
    'settings' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Licence No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('licence' => $row['id']),
                    'licence'
                ) . '">' . $row['licNo'] . '</a>';
            }
        ),
        array(
            'title' => 'Type',
            'formatter' => 'LicenceTypeShort',
        ),
        array(
            'title' => 'Start date',
            'formatter' => 'Date',
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
    )
);
