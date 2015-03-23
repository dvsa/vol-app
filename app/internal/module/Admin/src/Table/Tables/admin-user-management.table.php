<?php

return array(
    'variables' => array(
        'title' => 'Users'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'requireRows' => false),
                'edit' => array('class' => 'secondary', 'requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
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
            'title' => 'Username',
            'name' => 'loginId'
        ),
        array(
            'title' => 'Name',
            'formatter' => function ($data, $column, $sm) {
                return $data['contactDetails']['person']['forename'] . ' ' .
                $data['contactDetails']['person']['familyName'];
            }

        ),
        array(
            'title' => 'Email address',
            'name' => 'emailAddress',
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data) {
                return 'TODO';
            }
        ),
        array(
            'title' => 'Role',
            'formatter' => function ($data) {
                return 'TODO';
            }
        ),
        array(
            'title' => 'Last login',
            'name' => 'lastSuccessfulLoginDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
