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
            'name' => 'id_col'
        ),
        array(
            'title' => 'Category',
            'name' => 'cat_description',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'task_sub_type',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
        ),
        array(
            'title' => 'Date',
            'formatter' => 'Date',
            'name' => 'action_date'
        ),
        array(
            'title' => 'Owner',
            'name' => 'owner',
        ),
        array(
            'title' => 'Name',
            'name' => 'name_col',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
