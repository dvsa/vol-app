<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'outstanding-applications']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'oppositions',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'outstanding-applications'
    ),
    'columns' => array(
        array(
            'title' => 'Application No',
            'name' => 'id'
        ),
        array(
            'title' => 'Application type',
            'name' => 'applicationType',
        ),
        array(
            'title' => 'Received date',
            'name' => 'dateReceived',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'OOO/OOR',
            'formatter' => function ($data) {
                return 'todo';
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
