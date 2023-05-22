<?php

return array(
    'variables' => array(
        'title' => 'Statements',
        'titleSingular' => 'Statement',
        'empty_message' => 'There are no statements'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button', 'label' => 'Add statement'),
                'edit' => array('class' => 'govuk-button govuk-button--secondary js-require--one', 'requireRows' => true),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
                'delete' => array('class' => 'govuk-button govuk-button--secondary js-require--one', 'requireRows' => true)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
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
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
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
