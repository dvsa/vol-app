<?php

return array(
    'variables' => array(
        'title' => 'Statements',
        'empty_message' => 'There are no statements'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary', 'label' => 'Add statement'),
                'edit' => array('class' => 'action--secondary js-require--one', 'requireRows' => true),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
                'delete' => array('class' => 'action--secondary js-require--one', 'requireRows' => true)
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
            'formatter' => function ($data, $column, $sm) {
                return $data['requestorsContactDetails']['person']['forename'] . ' ' .
                    $data['requestorsContactDetails']['person']['familyName'];
            }
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
