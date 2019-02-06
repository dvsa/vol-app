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
            'name' => 'typeDescription',
        ],
        [
            'title' => 'Rec\'d Date',
            'name' => 'dateReceived',
            'formatter' => 'Date'
        ],
        [
            'title' => 'dashboard-table-permit-application-status',
            'name' => 'status',
            'formatter' => function ($row) {
                return $this->callFormatter(
                    [
                        'name' => 'status',
                        'formatter' => 'RefDataStatus',
                    ],
                    [
                        'status' => [
                            'id' => $row['statusId'],
                            'description' => $row['statusDescription'],
                        ],
                    ]
                );
            }
        ]
    ],
];
