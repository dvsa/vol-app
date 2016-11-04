<?php

return array(
    'variables' => array(
        'title' => 'Change history'
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
            'formatter' => 'EventHistoryDescription',
        ),
        array(
            'title' => 'Info',
            'name' => 'eventData',
        ),
        array(
            'title' => 'App. Id',
            'name' => 'appId',
            'formatter' => function ($row) {
                return isset($row['application']['id']) ? $row['application']['id'] : null;
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => 'DateTime',
            'sort' => 'eventDatetime',
        ),
        array(
            'title' => 'By',
            'formatter' => 'EventHistoryUser'
        )
    )
);
