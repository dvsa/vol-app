<?php

return array(
    'variables' => array(
        'title' => 'Exported reports',
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'name' => 'description',
            'formatter' => function ($row) {
                $column['formatter'] = 'DocumentDescription';
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ),
        array(
            'title' => 'Category',
            'name' => 'categoryName',
        ),
        array(
            'title' => 'Subcategory',
            'name' => 'documentSubCategoryName',
        ),
        array(
            'title' => 'Format',
            'name' => 'filename',
            'formatter' => function ($row) {
                $column['formatter'] = 'FileExtension';
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => 'Date'
        ),
    )
);
