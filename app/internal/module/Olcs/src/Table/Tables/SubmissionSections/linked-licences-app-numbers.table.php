<?php
return array(
    'variables' => array(
        'id' => 'linked-licences-app-numbers',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'linked-licences-app-numbers']
        ],
        'title' => 'Linked licences'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'persons',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'linked-licences-app-numbers'
    ),
    'columns' => array(
        array(
            'title' => 'Lic #',
            'name' => 'licNo'
        ),
        array(
            'title' => 'Status',
            'name' => 'status'
        ),
        array(
            'title' => 'Licence type',
            'name' => 'licenceType'
        ),
        array(
            'title' => 'Total V-auth',
            'name' => 'totAuthVehicles'
        ),
        array(
            'title' => 'Total T-auth',
            'name' => 'totAuthTrailers'
        ),
        array(
            'title' => 'Vehicles in possession',
            'name' => 'vehiclesInPossession'
        ),
        array(
            'title' => 'Trailers in possession',
            'name' => 'trailersInPossession'
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
