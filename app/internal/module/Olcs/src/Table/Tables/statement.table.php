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
                    array('action' => 'edit', 'statement' => $data['id']),
                    'case_statement',
                    true
                ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'dateRequested'
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
            'name' => 'dateStopped'
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
                return '<a href="' . $this->generateUrl(
                    array(
                        'controller' => 'Document',
                        'action' => 'retrieve',
                        'statement' => $data['id'],
                        'format' => 'rtf',
                        'country' => 'en_GB',
                        'filename' => 'generated_S43_Letter',
                        'template' => 'S43_Letter'
                    ),
                    'document_retrieve',
                    true
                ) . '">Generate Letter</a>';
            },
            'name' => 'dateRequested'
        ),
    )
);
