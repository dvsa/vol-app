<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'people']
        ],
        'title' => 'people',
        'id' => 'people'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'people',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'people'
    ),
    'columns' => array(
        array(
            'title' => 'Title',
            'name' => 'title'
        ),
        array(
            'title' => 'Firstname',
            'name' => 'forename'
        ),
        array(
            'title' => 'Surname',
            'name' => 'familyName'
        ),
        array(
            'title' => 'DOB',
            'formatter' => 'Date',
            'name' => 'birthDate'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ),
    )
);
