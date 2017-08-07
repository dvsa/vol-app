<?php

return array(
    'variables' => array(
        'title' => 'Community Licence'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--secondary', 'value' => 'Add'),
                'office-licence-add' => array('class' => 'action--secondary', 'value' => 'Add office licence')
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Prefix',
            'sort' => 'prefix',
            'name' => 'serialNoPrefix',
        ),
        array(
            'title' => 'Date Issued',
            'formatter' => 'Date',
            'sort' => 'specifiedDate',
            'name' => 'specifiedDate',
        ),
        array(
            'title' => 'Issue number',
            'sort' => 'issueNo',
            'name' => 'issueNo',
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'RefData'
        ),
        [
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => [
                'filename'
            ],
        ],
    )
);
