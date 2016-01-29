<?php

return array(
    'variables' => array(
        'title' => 'Access history'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'User',
            'formatter' => 'StackValueReplacer',
            'stringFormat' => '{user->contactDetails->person->forename} {user->contactDetails->person->familyName}'
        )
    )
);
