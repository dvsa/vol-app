<?php

return [
    'variables' => [
        'title' => 'Permit Applications',
        'id' => 'permit-applications-table',
        'empty_message' => 'There are no permit application records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'Apply' => ['class' => 'action--primary']
            ],
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'dashboard-table-permit-application-ref',
            'name' => 'id',
            'formatter' => 'InternalLicencePermitReference'
        ],
        [
            'title' => 'dashboard-table-permit-application-num',
            'name' => 'permitsRequired',
        ],
        [
            'title' => 'dashboard-table-permit-application-type',
            'name' => 'permitType',
            'formatter' => 'RefData'
        ],
        [
            'title' => 'Rec\'d Date',
            'name' => 'dateReceived',
            'formatter' => 'Date'
        ],
        [
            'title' => 'dashboard-table-permit-application-status',
            'name' => 'status',
            'formatter' => 'RefDataStatus'
        ]
    ],
];
