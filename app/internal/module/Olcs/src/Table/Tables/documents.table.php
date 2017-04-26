<?php

return [
    'variables' => [
        'title' => 'Docs & attachments'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'upload' => ['class' => 'action--primary'],
                'New letter' => [],
                'split' => ['class' => 'action--secondary', 'requireRows' => true],
                'relink' => [
                    'class' => 'action--secondary js-require--multiple',
                    'requireRows' => true,
                ],
                'delete' => [
                    'class' => 'action--delete action--secondary js-require--multiple',
                    'requireRows' => true,
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => 'DocumentDescription',
        ],
        [
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName'
        ],
        [
            'title' => 'Subcategory',
            'name' => 'documentSubCategoryName',
            'sort' => 'documentSubCategoryName',
            'formatter' => 'DocumentSubcategory'
        ],
        [
            'title' => 'Format',
            'formatter' => 'FileExtension'
        ],
        [
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => 'DateTime',
            'sort' => 'issuedDate',
        ],
        [
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'data-attributes' => [
                'filename'
            ],
        ],
    ],
];
