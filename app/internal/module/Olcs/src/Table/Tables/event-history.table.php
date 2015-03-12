<?php

return array(
    'variables' => array(
        'title' => 'Result list'
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
            'title' => 'Event date / time',
            'sort' => 'appId'
        ),
    )
);
