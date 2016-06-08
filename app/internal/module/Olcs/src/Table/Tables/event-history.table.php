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
                if (isset($row['user']['contactDetails']['person']['forename']) &&
                    isset($row['user']['contactDetails']['person']['familyName'])) {
                    $by = $row['user']['contactDetails']['person']['forename'] . ' '
                        . $row['user']['contactDetails']['person']['familyName'];
                } else {
                    $by = $row['user']['loginId'];
                }
                return $by;
            },
        )
    )
);
