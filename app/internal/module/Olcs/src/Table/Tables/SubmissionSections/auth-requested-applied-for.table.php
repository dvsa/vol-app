<?php

return array(
    'variables' => array(
        'id' => 'auth-requested-applied-for',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'auth-requested-applied-for']
        ],
        'title' => 'Authorisation requested / applied for'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'auth-requested-applied-for',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'auth-requested-applied-for'
    ),
    'columns' => array(
        array(
            'title' => 'Application ID',
            'name' => 'id'
        ),
        array(
            'title' => 'Current VIP',
            'name' => 'currentVehiclesInPossession'
        ),
        array(
            'title' => 'Current TIP',
            'name' => 'currentTrailersInPossession'
        ),
        array(
            'title' => 'Current vehicle authorisation',
            'name' => 'currentVehicleAuthorisation'
        ),
        array(
            'title' => 'Current trailer authorisation',
            'name' => 'currentTrailerAuthorisation'
        ),
        array(
            'title' => 'Requested vehicle authorisation',
            'name' => 'requestedVehicleAuthorisation'
        ),
        array(
            'title' => 'Requested trailer authorisation',
            'name' => 'requestedTrailerAuthorisation'
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
