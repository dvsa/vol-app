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
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => 'DateTime',
            'sort' => 'eventDatetime',
        ),
        array(
            'title' => 'By',
            'formatter' => function ($row) {
                return $row['user']['contactDetails']['person']['forename'] . ' '
                       . $row['user']['contactDetails']['person']['familyName'];
            },
        )
    )
);
