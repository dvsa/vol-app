<?php

use Common\Service\Table\Formatter\DashboardApplicationLink;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\TableBuilder;

$translationPrefix = 'dashboard-table-applications';

return [
    'variables' => [
        'title' => $translationPrefix,
        'hide_column_headers' => false,
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard-table-applications-appId',
            'lva' => 'application',
            'formatter' => DashboardApplicationLink::class
        ],
        [
            'title' => 'dashboard-table-applications-status',
            'formatter' => fn($row) =>
             /**
             * @var TableBuilder $this
             * @psalm-scope-this TableBuilder
             */
            $this->callFormatter(
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
            )
        ],
    ]
];
