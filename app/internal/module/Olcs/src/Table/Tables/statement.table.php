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
                    array('action' => 'edit', 'statement' => $data['id'])
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
        )
    )
);
