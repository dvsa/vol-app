<?php

return array(
    'variables' => array(
        'title' => 'Events'
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
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => 'Date',
            'sort' => 'createdOn',
        ),
        array(
            'title' => 'Description',
            'formatter' => function ($row) {
                return $row['eventHistoryType']['description'];
            },
        ),
        array(
            'title' => 'Data',
            'name' => 'eventData',
        ),
        array(
            'title' => 'User',
            'formatter' => function ($row) {
                return $row['user']['contactDetails']['person']['forename'] . ' '
                       . $row['user']['contactDetails']['person']['familyName'];
            },
        )
    )
);
