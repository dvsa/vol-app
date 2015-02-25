<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'penalties', 'subSection' => 'requested-penalties']
        ],
        'title' => 'Requested penalties'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'requested-penalties',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'requested-penalties'
    ),
    'columns' => array(
        array(
            'title' => 'Penalty type',
            'name' => 'penaltyType'
        ),
        array(
            'title' => 'Duration',
            'name' => 'duration'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
