<?php
return array(
    'variables' => array(
        'id' => 'licences',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-responsibilities', 'subSection' => 'licences']
        ],
        'title' => 'Licences'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'licences',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'licences'
    ),
    'columns' => array(
        array(
            'title' => 'Manager type',
            'name' => 'managerType',
        ),
        array(
            'title' => 'No. of operating centres',
            'name' => 'noOpCentres',
        ),
        array(
            'title' => 'Licence No.',
            'name' => 'licNo'
        ),
        array(
            'title' => 'Operator name',
            'name' => 'organisationName',
        ),
        array(
            'title' => 'Hours per week',
            'name' => 'hrsPerWeek',
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
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
