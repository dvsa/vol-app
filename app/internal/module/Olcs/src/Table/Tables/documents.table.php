<?php

use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\Formatter\DocumentSubcategory;
use Common\Service\Table\Formatter\FileExtension;

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
            'formatter' => DocumentDescription::class,
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
            'formatter' => DocumentSubcategory::class
        ],
        [
            'title' => 'Format',
            'formatter' => FileExtension::class
        ],
        [
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
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
