<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\TableBuilder;
use Common\Util\Escape;

return [
    'variables' => [
        'titleSingular' => 'Document Template',
        'title' => 'Document Templates',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = DocumentDescription::class;
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ],
        [
            'title' => 'Category',
            'name' => 'category',
            'sort' => 'category',
            'formatter' => fn($row) => empty($row['categoryName']) ? '' : Escape::html($row['categoryName']),
        ],
        [
            'title' => 'Subcategory',
            'name' => 'subCategory',
            'sort' => 'subCategory',
            'formatter' => fn($row) => empty($row['subCategoryName']) ? '' : Escape::html($row['subCategoryName']),
        ],
        [
            'title' => 'Identifier',
            'name' => 'filename',
            'sort' => 'filename',
            'formatter' => fn($row) => Escape::html(ltrim($row['filename'], '/')),
        ],
        [
            'title' => 'Edited date',
            'name' => 'lastModifiedOn',
            'sort' => 'lastModifiedOn',
            'formatter' => function ($row, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Date::class;
                return empty($row['lastModifiedOn']) ? 'N/A' : $this->callFormatter($column, $row);
            }
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
