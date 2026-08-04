<?php

use Common\Controller\Lva\AbstractSafetyController;
use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\YesNo;

$translationPrefix = 'safety-inspection-providers.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.caption',
        'empty_message' => $translationPrefix . '.hint',
        'required_label' => 'safety inspection provider',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'Add safety inspector'],
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => AbstractSafetyController::DEFAULT_TABLE_RECORDS_COUNT,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.providerName',
            'action' => 'edit',
            'stack' => 'contactDetails->fao',
            'formatter' => StackValue::class,
            'type' => 'Action',
            'keepForReadOnly' => true,
        ],
        [
            'title' => $translationPrefix . '.external',
            'name' => 'isExternal',
            'formatter' => YesNo::class
        ],
        [
            'title' => $translationPrefix . '.address',
            'formatter' => Address::class,
            'name' => 'contactDetails->address'
        ],
        [
            'title' => 'markup-table-th-remove',
            'ariaDescription' => static fn($row) => $row['contactDetails']['fao'] ?? 'safety inspector',
            'type' => 'ActionLinks',
        ],
    ]
];
