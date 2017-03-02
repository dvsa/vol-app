<?php

return array(
    'variables' => array(
        'id' => 'imposed-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'imposed-penalties']
        ],
        'title' => 'Imposed penalties'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'imposed-penalties',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'imposed-penalties'
    ),
    'columns' => array(
        array(
            'title' => 'Final decision date',
            'name' => 'finalDecisionDate'
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
            'title' => 'Executed',
            'name' => 'executed'
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
