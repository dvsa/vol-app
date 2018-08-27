<?php

return array(
    'variables' => array(
        'title' => '',
        'empty_message' => '',
        'hide_column_headers' => true,
    ),
    'settings' => array(),
    'attributes' => array('class' => 'under-consideration'),
    'columns' => array(
        array(
            'title' => 'applicationDetailsTitle',
            'name' => 'applicationDetailsTitle',
            'formatter' => function ($data) {
                return '<b>' . $data['applicationDetailsTitle'] . '</b>';
            },
        ),
        array(
            'title' => 'applicationDetailsAnswer',
            'name' => 'applicationDetailsAnswer',
            'formatter' => 'Translate'
        )
    )
);
