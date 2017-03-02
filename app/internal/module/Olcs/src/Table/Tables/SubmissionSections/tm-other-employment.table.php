<?php
return array(
    'variables' => array(
        'id' => 'tm-other-employment',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-other-employment']
        ],
        'title' => 'Other employment'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'tm-other-employment',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'tm-other-employment'
    ),
    'columns' => array(
        array(
            'title' => 'Employer',
            'name' => 'employerName',
        ),
        array(
            'title' => 'Address',
            'width' => '350px',
            'formatter' => 'Address',
            'name' => 'address'
        ),
        array(
            'title' => 'Position',
            'name' => 'position'
        ),
        array(
            'title' => 'Hours/Days',
            'name' => 'hoursPerWeek'
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
