<?php

return array(
    'variables' => array(
        'title' => 'Tasks'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Link',
            'formatter' => 'TaskIdentifier'
        ),
        array(
            'title' => 'Category',
            'name' => 'category',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'subCategory',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
        ),
        array(
            'title' => 'Date',
            'formatter' => 'Date',
            'name' => 'date'
        ),
        array(
            'title' => 'Owner',
            'name' => 'owner',
        ),
        array(
            'title' => 'Name',
            'name' => 'name',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
