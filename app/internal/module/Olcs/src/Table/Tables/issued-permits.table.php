<?php

return [
    'variables' => [
        'title' => 'Issued Permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'columns' => [
        [
            'title' => 'dashboard-table-permit-application-ref',
            'name' => 'id',
            'formatter' => 'IssuedPermitLicencePermitReference'
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
