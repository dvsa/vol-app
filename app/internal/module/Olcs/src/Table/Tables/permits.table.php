<?php

return [
    'variables' => [
        'title' => 'Permit Applications',
        'empty_message' => 'There are no permit application records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'Apply' => ['class' => 'action--primary'],
                'Edit' => ['requireRows' => true, 'class' => 'action--secondary js-require--one']
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
