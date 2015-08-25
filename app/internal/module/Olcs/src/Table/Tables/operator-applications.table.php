<?php

return array(
    'variables' => array(
        'title' => 'Applications'
    ),
    'settings' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Licence/App No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('application' => $row['id']),
                    'lva-application'
                ) . '">' . $row['licence']['licNo'] .'/'. $row['id'] . '</a>';
            }
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($row) {
                return $row['isVariation'] ? 'Variation' : 'New';
            }
        ),
        array(
            'title' => 'Received',
            'formatter' => 'Date',
            'name' => 'receivedDate'
        ),
        array(
            'title' => 'Status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
    )
);
