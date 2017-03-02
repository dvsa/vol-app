<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'people']
        ],
        'title' => 'People',
        'id' => 'people'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'people',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
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
            'name' => 'birthDate'
        ),
        array(
            'title' => 'Disqual.',
            'name' => 'disqualificationStatus'
        ),
        array(
            'type' => 'Checkbox',
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ),
    )
);
