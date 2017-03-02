<?php

return array(
    'variables' => array(
        'title' => 'Tasks',
        'titleSingular' => 'Task',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'create task' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                're-assign task' => array('requireRows' => true, 'class' => 'action--secondary js-require--multiple'),
                'close task' => array('requireRows' => true, 'class' => 'action--secondary js-require--multiple')
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
            'sort' => 'linkDisplay',
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName',
        ),
        array(
            'title' => 'Sub category',
            'name' => 'taskSubCategoryName',
            'sort' => 'taskSubCategoryName',
        ),
        array(
            'title' => 'Description',
            'formatter' => 'TaskDescription',
            'sort' => 'description',
        ),
        array(
            'title' => 'Date',
            'name' => 'actionDate',
            'formatter' => 'TaskDate',
            'sort' => 'actionDate',
        ),
        array(
            'title' => 'Owner',
            'formatter' => 'TaskOwner',
            'sort' => 'teamName,ownerName',
        ),
        array(
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'formatter' => 'TaskCheckbox',
        )
    )
);
