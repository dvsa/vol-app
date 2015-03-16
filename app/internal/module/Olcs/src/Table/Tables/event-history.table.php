<?php

return array(
    'variables' => array(
        'title' => 'Result list'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
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
            'title' => 'I.D.',
            'name' => 'id',
            'sort' => ''
        ),
        array(
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => 'DateTime',
            'sort' => ''
        ),
        array(
            'title' => 'Description',
            'name' => 'eventDescription',
            'sort' => ''
        ),
        array(
            'title' => 'Operation',
            'name' => 'operation',
            'sort' => ''
        )
    )
);
