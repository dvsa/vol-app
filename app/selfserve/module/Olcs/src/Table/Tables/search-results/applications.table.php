<?php

return array(
    'variables' => array(
        'title' => 'applications',
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Application ID',
            'name' => 'applicationId'
        ),
        array(
            'title' => 'Received',
            'formatter' => 'Date',
            'name' => 'receivedDate',
        ),
        array(
            'title' => 'Published',
            'formatter' => 'Date',
            'name' => 'publishedDate',
        ),
        array(
            'title' => 'Granted',
            'formatter' => 'Date',
            'name' => 'grantedDate',
        ),
        array(
            'title' => 'Objections',
            'formatter' => function ($data, $column) {
                if (!isset($data['objections']) || empty($data['objections'])) {
                    return 'N/A';
                }
                return $data['objections'];
            }
        ),
        array(
            'title' => 'Representations',
            'name' => 'representations',
            'formatter' => function ($data, $column) {
                if (!isset($data['representations']) || empty($data['representations'])) {
                    return 'N/A';
                }
                return $data['representations'];
            }
        )
    )
);
