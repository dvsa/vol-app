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
                'delete' => array('class' => 'secondary', 'requireRows' => true)
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
                ) . '" class="js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'requestedDate'
        ),
        array(
            'title' => 'Requested by',
            'format' => '{{requestorsForename}} {{requestorsFamilyName}}'
        ),
        array(
            'title' => 'Statement type',
            'formatter' => function ($data, $column, $sm) {

                return $data['statementType']['description'];
            },
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
            'formatter' => function ($data, $column, $sl) {
                $column['formatter'] = 'Date';
                return (!empty($data['issuedDate']) ?
                    $this->callFormatter($column, $data) :
                    $sl->get('translator')->translate('Not issued')
                );
            },
            'name' => 'issuedDate'
        ),
        array(
            'title' => 'VRM',
            'name' => 'vrm'
        ),
    )
);
