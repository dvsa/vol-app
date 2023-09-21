<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\Formatter\Translate;
use Common\Service\Table\Formatter\YesNo;

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
            'formatter' => Date::class
        ],
        [
            'title' => $translationPrefix . '.datePublished',
            'name' => 'publishedDate',
            'formatter' => Date::class
        ],
        [
            'title' => $translationPrefix  . '.publicationNumber',
            'isNumeric' => true,
            'name' => 'publicationNo'
        ],
        [
            'title' => $translationPrefix . '.grantDate',
            'name' => 'grantedDate',
            'formatter' => Date::class
        ],
        [
            'title' => $translationPrefix . '.oooDate',
            'name' => 'oooDate',
            'formatter' => Translate::class,
        ],
        [
            'title' => $translationPrefix . '.oorDate',
            'name' => 'oorDate',
            'formatter' => Translate::class,
        ],
        [
            'title' => $translationPrefix . '.objectionRepresentationMade',
            'name' => 'isOpposed',
            'formatter' => YesNo::class,
        ],
        [
            'title' => 'interim-status',
            'name' => 'interimStatus',
            'formatter' => RefData::class,
        ]
    ]
];
