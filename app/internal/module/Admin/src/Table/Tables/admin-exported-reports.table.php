<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\Formatter\FileExtension;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'Exported report',
        'title' => 'Exported reports',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Description',
            'name' => 'description',
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
            'name' => 'categoryName',
        ],
        [
            'title' => 'Subcategory',
            'name' => 'documentSubCategoryName',
        ],
        [
            'title' => 'Format',
            'name' => 'filename',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = FileExtension::class;
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ],
        [
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => Date::class
        ],
    ]
];
