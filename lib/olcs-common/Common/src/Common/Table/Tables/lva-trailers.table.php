<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;

$translationPrefix = 'licence_goods-trailers_trailer.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.tableEmptyMessage',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [],
                'delete' => [
                    'label' => 'action_links.remove',
                    'requireRows' => true
                ]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.trailerNo',
            'name' => 'trailerNo',
            'action' => 'edit',
            'type' => 'Action',
            'keepForReadOnly' => true,
        ],
        [
            'title' => $translationPrefix . '.specified',
            'formatter' => Date::class,
            'name' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.longerSemiTrailer',
            'formatter' => YesNo::class,
            'name' => 'isLongerSemiTrailer'
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'trailerNo',
            'type' => 'ActionLinks',
        ],
        [
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ]
    ]
];
