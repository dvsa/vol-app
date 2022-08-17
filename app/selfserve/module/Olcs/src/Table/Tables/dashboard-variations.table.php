<?php

$translationPrefix = 'dashboard-table-variations';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'hide_column_headers' => false,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'dashboard-table-variations-appId',
            'isNumeric' => true,
            'lva' => 'variation',
            'formatter' => 'DashboardApplicationLink'
        ),
        array(
            'title' => 'dashboard-table-variations-status',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'status',
                        'formatter' => 'RefDataStatus',
                    ],
                    [
                        'status' => [
                            'id' => $row['status']['id'],
                            'description' => $row['status']['description'],
                        ],
                    ]
                );
            }
        ),
    )
);
