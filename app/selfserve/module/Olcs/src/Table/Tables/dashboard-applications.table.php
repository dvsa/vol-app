<?php

$translationPrefix = 'dashboard-table-applications';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'hide_column_headers' => false,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'dashboard-table-applications-appId',
            'isNumeric' => true,
            'lva' => 'application',
            'formatter' => 'DashboardApplicationLink'
        ),
        array(
            'title' => 'dashboard-table-applications-status',
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
