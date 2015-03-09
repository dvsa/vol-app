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
                'delete' => array('class' => 'secondary js-require--multiple', 'requireRows' => true)
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
                return $data['documentSubCategoryName'] . ($data['isDigital'] == 1 ? ' (digital)' : '');
            },
        ),
        array(
            'title' => 'Format',
            'name' => 'documentType',
            'sort' => 'documentType'
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => 'DateTime',
            'sort' => 'issuedDate',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
