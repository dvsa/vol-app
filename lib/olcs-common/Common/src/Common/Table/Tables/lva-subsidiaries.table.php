<?php

return [
    'variables' => [
        'title' => 'application_your-business_business_details-subsidiaries-tableHeader',
        'empty_message' => 'application_your-business_business_details-subsidiaries-tableEmptyMessage',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'Add subsidiary'],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'application_your-business_business_details-subsidiaries-columnName',
            'name' => 'name',
            'action' => 'edit',
            'type' => 'Action',
            'keepForReadOnly' => true,
        ],
        [
            'title' => 'application_your-business_business_details-subsidiaries-columnCompanyNo',
            'name' => 'companyNo'
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'name',
            'type' => 'ActionLinks',
        ],
    ]
];
