<?php

return array(
    'variables' => array(
        'title' => 'Partner organisations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'requireRows' => false),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--one')
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
            'title' => 'Name',
            'name' => 'description'
        ),
        array(
            'title' => 'Address',
            'formatter' => 'Address',
            'name' => 'address',
            'addressFields' => 'FULL',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
