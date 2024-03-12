<?php

use Common\Service\Table\Formatter\SystemInfoMessageLink;

return [
    'variables' => [
        'title' => 'System messages',
        'titleSingular' => 'System message',
        'empty_message' => 'You don\'t have messages',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false,
                    'label' => 'Add message',
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Message',
            'sort' => 'description',
            'action' => 'edit',
            'formatter' => SystemInfoMessageLink::class,
        ],
        [
            'title' => 'Displayed to',
            'sort' => 'isInternal',
            'formatter' => fn($row) => $row['isInternal'] === 'Y'
                ? 'Internal'
                : 'Self serve'
        ],
        [
            'title' => 'Start',
            'name' => 'startDate',
            'sort' => 'startDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'dateformat' => 'H:i d/m/Y',
        ],
        [
            'title' => 'End',
            'name' => 'endDate',
            'sort' => 'endDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'dateformat' => 'H:i d/m/Y',
        ],
        [
            'type' => 'Action',
            'action' => 'delete',
            'text' => 'Remove',
        ],
    ],
];
