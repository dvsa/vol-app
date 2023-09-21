<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\Formatter\FileExtension;

return array(
    'variables' => array(
        'titleSingular' => 'Exported report',
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
                $column['formatter'] = DocumentDescription::class;
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
                $column['formatter'] = FileExtension::class;
                return $this->callFormatter(
                    $column,
                    $row
                );
            }
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => Date::class
        ),
    )
);
