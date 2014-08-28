<?php

return array(
    'variables' => array(
        'title' => 'Documents'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'upload' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'name' => 'description',
            'formatter' => function ($row) {
                return '<a href=#>' . $row['description'] . '</a>';
            }
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'subCategoryName',
        ),
        array(
            'title' => 'Description',
            'name' => 'subCategoryName',
        ),
        array(
            'title' => 'Date',
            'name' => 'date',
            'formatter' => 'Date',
            'sort' => 'date',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
