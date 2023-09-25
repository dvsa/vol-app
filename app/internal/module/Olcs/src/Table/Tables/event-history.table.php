<?php

use Common\Service\Table\Formatter\EventHistoryDescription;
use Common\Service\Table\Formatter\EventHistoryUser;

return array(
    'variables' => array(
        'title' => 'Change history entries',
        'titleSingular' => 'Change history entry',
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'actions' => array()
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Details',
            'formatter' => EventHistoryDescription::class,
        ),
        array(
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
        ),
        array(
            'title' => 'App. Id',
            'isNumeric' => true,
            'name' => 'appId',
            'formatter' => function ($row) {
                return isset($row['application']['id']) ? $row['application']['id'] : null;
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'sort' => 'eventDatetime',
        ),
        array(
            'title' => 'By',
            'formatter' => EventHistoryUser::class
        )
    )
);
