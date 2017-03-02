<?php

return array(
    'variables' => array(
        'title' => 'Inspection requests',
        'titleSingular' => 'Inspection request'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'formName' => 'inspectionReport',
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array(
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one',
                    'label' => 'Remove'
                )
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'ID',
            'sort' => 'id',
            'name' => 'id',
            'formatter' => 'InspectionRequestId'
        ),
        array(
            'title' => 'Report type',
            'formatter' => function ($row) {
                return $row['reportType']['description'];
            },
            'name' => 'reportType',
            'sort' => 'reportType'
        ),
        array(
            'title' => 'Date requested',
            'name' => 'requestDate',
            'formatter' => 'Date',
            'sort' => 'requestDate'
        ),
        array(
            'title' => 'Due date',
            'name' => 'dueDate',
            'formatter' => 'Date',
            'sort' => 'duetDate'
        ),
        array(
            'title' => 'Application ID',
            'formatter' => function ($row) {
                return $row['application']['id'];
            },
            'name' => 'applicationId',
            'sort' => 'applicationId'
        ),
        array(
            'title' => 'Result status',
            'formatter' => function ($row) {
                return $row['resultType']['description'];
            },
            'name' => 'resultType',
            'sort' => 'resultType'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
