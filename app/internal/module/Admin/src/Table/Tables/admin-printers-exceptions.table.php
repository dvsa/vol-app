<?php

use Common\Service\Table\Formatter\PrinterDocumentCategory;
use Common\Service\Table\Formatter\PrinterException;

return [
    'variables' => [
        'title' => ' Printers exceptions',
        'titleSingular' => ' Printers exception',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'formName' => 'printerExceptions',
            'actions' => [
                'addRule' => [
                    'class' => 'govuk-button',
                    'requireRows' => false,
                    'label' => 'add'
                ],
                'editRule' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'edit'
                ],
                'deleteRule' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--warning js-require--one',
                    'label' => 'delete'
                ]
            ]
        ],
        'showTotal' => true
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Exception',
            'name' => 'exception',
            'formatter' => PrinterException::class
        ],
        [
            'title' => 'Document categories',
            'name' => 'documentCategories',
            'formatter' => PrinterDocumentCategory::class
        ],
        [
            'title' => 'Designated printer',
            'name' => 'printerName',
            'sort' => 'printerName',
            'formatter' => fn($row) => $row['printer']['printerName'],
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
