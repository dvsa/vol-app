<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'applied-penalties']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'applied-penalties',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'applied-penalties'
    ),
    'columns' => array(
        array(
            'title' => 'Penalty ID',
            'name' => 'id'
        ),
        array(
            'title' => 'Penalty type',
            'name' => 'penaltyType'
        ),
        array(
            'title' => 'Start Date',
            'formatter' => 'Date',
            'name' => 'startDate'
        ),
        array(
            'title' => 'End Date',
            'formatter' => 'Date',
            'name' => 'endDate'
        ),
        array(
            'title' => 'Imposed',
            'name' => 'imposed'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
