<?php

use Common\Service\Table\Formatter\DashboardApplicationLink;
use Common\Service\Table\Formatter\RefDataStatus;

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
            'lva' => 'variation',
            'formatter' => DashboardApplicationLink::class
        ),
        array(
            'title' => 'dashboard-table-variations-status',
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
