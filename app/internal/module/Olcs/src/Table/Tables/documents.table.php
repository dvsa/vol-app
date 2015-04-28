<?php

return array(
    'variables' => array(
        'title' => 'Docs & attachments'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'upload' => array('class' => 'primary'),
                'New letter' => array(),
                'delete' => array('class' => 'secondary js-require--multiple', 'requireRows' => true),
                'split' => array('class' => 'secondary', 'requireRows' => true),
            )
        ),
        'paginate' => array(
            'limit' => array(
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => 'DocumentDescription',
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName'
        ),
        array(
            'title' => 'Subcategory',
            'name' => 'documentSubCategoryName',
            'sort' => 'documentSubCategoryName',
            'formatter' => function ($data, $column) {
                // @TODO move to external formatter
                return $data['documentSubCategoryName'] . ($data['isExternal'] == 1 ? ' (selfserve)' : '');
            },
        ),
        array(
            'title' => 'Format',
            'formatter' => function ($data) {
                // @TODO move to external formatter, make more robust
                return strtoupper(substr($data['filename'], strrpos($data['filename'], '.') + 1));
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => 'DateTime',
            'sort' => 'issuedDate',
        ),
        array(
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => array(
                'filename'
            )
        )
    )
);
