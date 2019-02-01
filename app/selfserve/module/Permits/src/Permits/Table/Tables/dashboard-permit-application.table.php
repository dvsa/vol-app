<?php

return array(
    'variables' => array(
        'title' => 'dashboard-table-permit-application-title',
        'empty_message' => 'dashboard-no-permit-text',
        'hide_column_headers' => false,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'dashboard-table-permit-application-ref',
            'name' => 'id',
            'formatter' => 'LicencePermitReference',
        ),
        array(
            'title' => 'dashboard-table-permit-application-num',
            'name' => 'permitsRequired',
            'formatter' => 'NullableNumber'
        ),
        array(
            'title' => 'dashboard-table-permit-application-type',
            'name' => 'typeDescription',
        ),
        array(
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
        )
    )
);
