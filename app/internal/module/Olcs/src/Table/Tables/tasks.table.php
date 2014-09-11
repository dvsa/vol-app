<?php

return array(
    'variables' => array(
        'title' => 'Tasks'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'create task' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                're-assign task' => array('requireRows' => true)
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
            'title' => 'Link',
            'formatter' => 'TaskIdentifier',
            'name' => 'link',
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'taskSubCategoryName',
        ),
        array(
            'title' => 'Description',
            'formatter' => function ($row) {
                return '<a href=#>' . $row['description'] . '</a>';
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'actionDate',
            'formatter' => 'TaskDate',
            'sort' => 'actionDate',
        ),
        array(
            'title' => 'Owner',
            'formatter' => function ($row) {
                if (empty($row['ownerName'])) {
                    return 'Unassigned';
                }
                return $row['ownerName'];
            }
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
