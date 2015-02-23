<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-qualifications']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'tm-qualifications',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'tm-qualifications'
    ),
    'columns' => array(
        array(
            'title' => 'Type',
            'name' => 'tmType',
        ),
        array(
            'title' => 'Serial no.',
            'name' => 'serialNo',
        ),
        array(
            'title' => 'Date',
            'name' => 'convictionDate',
            'formatter' => 'date'
        ),
        array(
            'title' => 'Country',
            'name' => 'country',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
