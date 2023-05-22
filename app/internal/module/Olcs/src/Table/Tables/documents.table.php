<?php

return [
    'variables' => [
        'title' => 'Docs & attachments'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'upload' => ['class' => 'govuk-button'],
                'New letter' => [],
                'split' => ['class' => 'govuk-button govuk-button--secondary', 'requireRows' => true],
                'relink' => [
                    'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                    'requireRows' => true,
                ],
                'delete' => [
                    'class' => 'govuk-button govuk-button--warning js-require--multiple',
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
