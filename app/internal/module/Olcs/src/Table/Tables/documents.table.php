<?php

return array(
    'variables' => array(
        'title' => 'Documents & Attachments'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'upload' => array('class' => 'primary'),
                'New letter' => array(),
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
            'sort' => 'description',
            'formatter' => function ($data, $column) {
                $url = $this->generateUrl(
                    array(
                        'id' => $data['document'],
                        'filename' => $data['filename']
                    ),
                    'fetch_document'
                );
                return '<a href="' . $url . '">' . $data['description'] . '</a>';
            },
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName'
        ),
        array(
            'title' => 'Sub category',
            'name' => 'documentSubCategoryName',
            'sort' => 'documentSubCategoryName',
            'formatter' => function ($data, $column) {
                return $data['documentSubCategoryName'] . ($data['isDigital'] == 1 ? ' (digital)' : '');
            },
        ),
        array(
            'title' => 'Format',
            'name' => 'fileExtension',
            'sort' => 'fileExtension'
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
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
