<?php

use Common\Service\Table\Formatter\PrinterDocumentCategory;
use Common\Service\Table\Formatter\PrinterException;

return array(
    'variables' => array(
        'title' => ' Printers exceptions',
        'titleSingular' => ' Printers exception',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'printerExceptions',
            'actions' => array(
                'addRule' => array(
                    'class' => 'govuk-button', 
                    'requireRows' => false, 
                    'label' => 'add'
                ),
                'editRule' => array(
                    'requireRows' => true, 
                    'class' => 'govuk-button govuk-button--secondary js-require--one', 
                    'label' => 'edit'
                ),
                'deleteRule' => array(
                    'requireRows' => true, 
                    'class' => 'govuk-button govuk-button--warning js-require--one',
                    'label' => 'delete'
                )
            )
        ),
        'showTotal' => true
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Exception',
            'name' => 'exception',
            'formatter' => PrinterException::class
        ),
        array(
            'title' => 'Document categories',
            'name' => 'documentCategories',
            'formatter' => PrinterDocumentCategory::class
        ),
        array(
            'title' => 'Designated printer',
            'name' => 'printerName',
            'sort' => 'printerName',
            'formatter' => function ($row) {
                return $row['printer']['printerName'];
            },
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
