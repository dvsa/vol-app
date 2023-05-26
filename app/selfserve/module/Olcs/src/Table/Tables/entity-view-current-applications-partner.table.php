<?php

$translationPrefix = 'entity-view-table-current-applications.table';

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [

        [
            'title' => $translationPrefix . '.variationNumber',
            'isNumeric' => true,
            'name' => 'id'
        ],
        [
            'title' => $translationPrefix . '.dateReceived',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ],
        [
            'title' => $translationPrefix . '.datePublished',
            'name' => 'publishedDate',
            'formatter' => 'Date'
        ],
        [
            'title' => $translationPrefix  . '.publicationNumber',
            'isNumeric' => true,
            'name' => 'publicationNo'
        ],
        [
            'title' => $translationPrefix . '.grantDate',
            'name' => 'grantedDate',
            'formatter' => 'Date'
        ],
        [
            'title' => $translationPrefix . '.oooDate',
            'name' => 'oooDate',
            'formatter' => 'Translate',
        ],
        [
            'title' => $translationPrefix . '.oorDate',
            'name' => 'oorDate',
            'formatter' => 'Translate',
        ],
        [
            'title' => $translationPrefix . '.objectionRepresentationMade',
            'name' => 'isOpposed',
            'formatter' => 'YesNo',
        ],
        [
            'title' => 'interim-status',
            'name' => 'interimStatus',
            'formatter' => 'RefData',
        ]
    ]
];
