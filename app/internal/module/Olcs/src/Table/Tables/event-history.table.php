<?php

use Common\Service\Table\Formatter\EventHistoryDescription;
use Common\Service\Table\Formatter\EventHistoryUser;

return [
    'variables' => [
        'title' => 'Change history entries',
        'titleSingular' => 'Change history entry',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => []
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Details',
            'formatter' => EventHistoryDescription::class,
        ],
        [
            'title' => 'Info',
            'name' => 'eventData',
            'formatter' => function ($row) {
                $eventData = $row['eventData'];

                // if the eventData represents a document store path, extract the filename
                if (strpos($eventData, 'documents/') === 0) {
                    $eventDataComponents = explode('/', $eventData);
                    return $eventDataComponents[count($eventDataComponents) - 1];
                }

                return $eventData;
            }
        ],
        [
            'title' => 'App. Id',
            'isNumeric' => true,
            'name' => 'appId',
            'formatter' => fn($row) => $row['application']['id'] ?? null
        ],
        [
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'sort' => 'eventDatetime',
        ],
        [
            'title' => 'By',
            'formatter' => EventHistoryUser::class
        ]
    ]
];
