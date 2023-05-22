<?php
return array(
    'variables' => array(
        'id' => 'convictions-and-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-previous-history', 'subSection' => 'convictions-and-penalties']
        ],
        'title' => 'Convictions/penalties'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'convictions-and-penalties',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'convictions-and-penalties'
    ),
    'columns' => array(
        array(
            'title' => 'Offence',
            'name' => 'offence',
        ),
        array(
            'title' => 'Conviction date',
            'name' => 'convictionDate'
        ),
        array(
            'title' => 'Name of court',
            'name' => 'courtFpn'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty',
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
