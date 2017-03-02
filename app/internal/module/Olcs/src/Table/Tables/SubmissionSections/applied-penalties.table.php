<?php

return array(
    'variables' => array(
        'id' => 'applied-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'applied-penalties']
        ],
        'title' => 'Applied penalties'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'applied-penalties',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'action--class' => 'secondary', 'requireRows' => true)
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
            'name' => 'startDate'
        ),
        array(
            'title' => 'End Date',
            'name' => 'endDate'
        ),
        array(
            'title' => 'Imposed',
            'name' => 'imposed'
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
