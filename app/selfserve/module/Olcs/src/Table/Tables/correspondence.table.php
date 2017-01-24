<?php

return array(
    'variables' => array(
        'title' => 'dashboard-documents.table.title',
        'titleSingular' => 'dashboard-documents.table.title',
        'empty_message' => 'dashboard-documents-empty-message',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'correspondence',
            'actions' => array()
        ),
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50],
            ],
        ],
    ),
    'attributes' => [],
    'columns' => array(
        array(
            'title' => 'dashboard-correspondence.table.column.issuedDate',
            'formatter' => function ($row) {
                if (isset($row['correspondence']['document']['issuedDate'])) {
                    return date(\DATE_FORMAT, strtotime($row['correspondence']['document']['issuedDate']));
                }
                return '';
            },
            'sort' => 'd.issuedDate'
        ),
        array(
            'title' => 'dashboard-correspondence.table.column.title',
            'name' => 'correspondence',
            'formatter' => 'AccessedCorrespondence',
            'sort' => 'd.description'
        ),
        array(
            'title' => 'dashboard-correspondence.table.column.reference',
            'name' => 'licence',
            'formatter' => 'LicenceNumberLink',
            'sort' => 'l.licNo'
        ),
    )
);
