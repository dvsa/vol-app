<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-responsibilities', 'subSection' => 'applications']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'applications',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'applications'
    ),
    'columns' => array(
        array(
            'title' => 'Manager type',
            'name' => 'managerType',
        ),
        array(
            'title' => 'No of Op Centres',
            'name' => 'noOpCentres',
        ),
        array(
            'title' => 'App Id',
            'name' => 'applicationId'
        ),
        array(
            'title' => 'Operator name',
            'name' => 'organisationName',
        ),
        array(
            'title' => 'Hrs per week',
            'name' => 'hrsPerWeek',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
