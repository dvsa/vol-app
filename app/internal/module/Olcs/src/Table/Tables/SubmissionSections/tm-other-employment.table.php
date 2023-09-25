<?php

use Common\Service\Table\Formatter\Address;

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
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true)
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
            'formatter' => Address::class,
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
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ),
    )
);
