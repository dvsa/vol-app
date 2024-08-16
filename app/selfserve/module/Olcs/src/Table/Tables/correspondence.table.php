<?php

use Common\Module;
use Common\Service\Table\Formatter\AccessedCorrespondence;
use Common\Service\Table\Formatter\LicenceNumberLink;

return [
    'variables' => [
        'title' => 'dashboard-documents.table.title',
        'titleSingular' => 'dashboard-documents.table.title',
        'empty_message' => 'dashboard-documents-empty-message',
    ],
    'settings' => [
        'crud' => [
            'formName' => 'correspondence',
            'actions' => []
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'dashboard-correspondence.table.column.issuedDate',
            'width' => '20%',
            'formatter' => function ($row) {
                if (isset($row['correspondence']['document']['issuedDate'])) {
                    return date(Module::$dateFormat, strtotime((string) $row['correspondence']['document']['issuedDate']));
                }
                return '';
            },
            'sort' => 'd.issuedDate'
        ],
        [
            'title' => 'dashboard-correspondence.table.column.title',
            'name' => 'correspondence',
            'formatter' => AccessedCorrespondence::class,
            'sort' => 'd.description'
        ],
        [
            'title' => 'dashboard-correspondence.table.column.reference',
            'name' => 'licence',
            'formatter' => LicenceNumberLink::class,
            'sort' => 'l.licNo'
        ],
    ]
];
