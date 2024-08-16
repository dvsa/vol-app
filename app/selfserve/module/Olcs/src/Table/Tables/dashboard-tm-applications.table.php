<?php

use Common\Service\Table\Formatter\DashboardTmActionLink;
use Common\Service\Table\Formatter\DashboardTmApplicationStatus;

return [
    'variables' => [
        'title' => '',
        'empty_message' => 'dashboard.tm-applications.table.EmptyMessage'
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard.tm-applications.table.column.operator.title',
            'name' => 'operatorName'
        ],
        [
            'title' => 'dashboard.tm-applications.table.column.app-no.title',
            'name' => 'applicationId'
        ],
        [
            'title' => 'dashboard-table-applications-status',
            'formatter' => DashboardTmApplicationStatus::class
        ],
        [
            'title' => 'dashboard.tm-applications.table.column.lic-no.title',
            'name' => 'licNo',
        ],
        [
            'title' => '',
            'formatter' => DashboardTmActionLink::class
        ]
    ]
];
