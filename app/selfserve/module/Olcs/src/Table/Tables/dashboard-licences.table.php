<?php

use Common\Service\Table\Formatter\LicenceNumberAndStatus;
use Common\Service\Table\Formatter\LicenceStatusSelfserve;
use Common\Service\Table\Formatter\Translate;

$translationPrefix = 'dashboard-table-licences';

return [
    'variables' => [
        'title' => $translationPrefix,
        'empty_message' => 'dashboard-no-licences-text',
        'hide_column_headers' => false,
    ],
    'settings' => ['layout' => 'dashboard-licences'],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard-table-licences-licNo',
            'name' => 'licNo',
            'formatter' => LicenceNumberAndStatus::class
        ],
        [
            'title' => 'dashboard-table-licences-status',
            'name' => 'status',
            'formatter' => LicenceStatusSelfserve::class,
        ],
        [
            'title' => 'dashboard-table-licences-licType',
            'name' => 'type',
            'formatter' => Translate::class
        ],
        [
            'title' => 'dashboard-table-licences-area',
            'name' => 'trafficArea',
            'formatter' => Translate::class
        ]
    ]
];
