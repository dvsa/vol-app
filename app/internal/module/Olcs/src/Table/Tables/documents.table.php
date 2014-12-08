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
                'delete' => array('class' => 'secondary', 'requireRows' => true)
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
                        'file' => $data['documentStoreIdentifier'],
                        'name' => $data['filename']
                    ),
                    'getfile'
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
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
