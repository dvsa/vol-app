<?php

return array(
    'variables' => array(
        'title' => 'Recipients'
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
            'title' => 'Contact Name',
            'name' => 'contactName',
            'sort' => 'contactName',
        ),
        array(
            'title' => 'Email',
            'name' => 'emailAddress',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
