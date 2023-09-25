<?php

use Common\Service\Table\Formatter\DashboardApplicationLink;
use Common\Service\Table\Formatter\RefDataStatus;

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
            'lva' => 'application',
            'formatter' => DashboardApplicationLink::class
        ),
        array(
            'title' => 'dashboard-table-applications-status',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'status',
                        'formatter' => RefDataStatus::class,
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
