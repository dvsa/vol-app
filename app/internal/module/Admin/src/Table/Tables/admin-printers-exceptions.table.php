<?php

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
                    'class' => 'action--primary', 
                    'requireRows' => false, 
                    'label' => 'add'
                ),
                'editRule' => array(
                    'requireRows' => true, 
                    'class' => 'action--secondary js-require--one', 
                    'label' => 'edit'
                ),
                'deleteRule' => array(
                    'requireRows' => true, 
                    'class' => 'action--secondary js-require--one', 
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
            'formatter' => 'PrinterException'
        ),
        array(
            'title' => 'Document categories',
            'name' => 'documentCategories',
            'formatter' => 'PrinterDocumentCategory'
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
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
