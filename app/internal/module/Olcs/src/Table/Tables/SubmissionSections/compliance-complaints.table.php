<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'compliance-complaints']
        ],
        'title' => 'Compliance complaints'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'compliance-complaints',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'columns' => array(
        array(
            'title' => 'Complaint date',
            'name' => 'complaintDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Complainant name',
            'formatter' => function ($data) {
                return $data['complainantForename'] . ' ' . $data['complainantFamilyName'];
            },
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
