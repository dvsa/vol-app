<?php

return array(
    'variables' => array(
        'title' => 'conditions-and-undertakings',
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'type',
            'formatter' => 'RefData',
            'name' => 'conditionType'
        ),
        array(
            'title' => 'description',
            'name' => 'description',
        ),
        array(
            'title' => 'added',
            'formatter' => 'Date',
            'name' => 'createdOn',
        ),
        array(
            'title' => 'status',
            'formatter' => 'RefData',
            'name' => 'status'
        )
    )
);
