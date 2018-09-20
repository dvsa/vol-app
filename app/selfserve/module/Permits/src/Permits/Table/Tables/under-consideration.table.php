<?php

return array(
    'variables' => array(
        'title' => 'Application details',
        'empty_message' => '',
        'hide_column_headers' => true,
    ),
    'settings' => array(),
    'attributes' => array('class' => 'under-consideration'),
    'columns' => array(
        array(
            'title' => 'applicationDetailsTitle',
            'name' => 'applicationDetailsTitle',
            'formatter' => function ($row, $column, $sm) {
                return '<b>' . $sm->get('translator')
                        ->translate($row['applicationDetailsTitle']) . '</b>';
            },
        ),
        array(
            'title' => 'applicationDetailsAnswer',
            'name' => 'applicationDetailsAnswer',
            'formatter' => 'Translate'
        )
    )
);
