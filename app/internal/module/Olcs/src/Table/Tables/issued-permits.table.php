<?php

return [
    'variables' => [
        'title' => 'Issued Permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [

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
            'title' => 'dashboard-table-permit-application-status',
            'name' => 'status',
            'formatter' => 'RefDataStatus'
        ],
        [
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => [
                'filename'
            ],
        ],
    ],
];
