<?php

return array(
    'variables' => array(
        'title' => 'Statements'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date requested',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    'case_statement',
                    true
                ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'requestedDate'
        ),
        array(
            'title' => 'Requested by',
            'format' => '{{requestorsForename}} {{requestorsFamilyName}}'
        ),
        array(
            'title' => 'Statement type',
            'name' => 'statementType'
        ),
        array(
            'title' => 'Date stopped',
            'formatter' => 'Date',
            'name' => 'stoppedDate'
        ),
        array(
            'title' => 'Requestor body',
            'name' => 'requestorsBody'
        ),
        array(
            'title' => 'Date issued',
            'formatter' => 'Date',
            'name' => 'issuedDate'
        ),
        array(
            'title' => 'VRM',
            'name' => 'vrm'
        ),
        array(
            'title' => 'Documents',
            'formatter' => function ($data, $column) {
                $filename =
                    $data['id'] . '_' .
                    'statement' . '_' .
                    'S43_Letter';
                return file_exists('/tmp/' . $filename . '.rtf') ? '<a href="' . $this->generateUrl(
                    array(
                        'controller' => 'Document',
                        'action' => 'retrieve',
                        'format' => 'rtf',
                        'country' => 'en_GB',
                        'filename' => $filename
                        ),
                    'document_retrieve',
                    true
                ) . '">'.$filename.'</a>' : '';
            },
            'name' => 'document'
        ),
    )
);
