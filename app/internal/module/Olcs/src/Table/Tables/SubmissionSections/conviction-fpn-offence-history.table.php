<?php

return array(
    'variables' => array(
        'id' => 'conviction-fpn-offence-history',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conviction-fpn-offence-history']
        ],
        'title' => 'Conviction / FPN / Offence history'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction-fpn-offence-history',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'conviction-fpn-offence-history'
    ),
    'columns' => array(
        array(
            'title' => 'Date of conviction',
            'formatter' => function ($data) {
                if ($data['convictionDate'] == null) {
                    return 'N/A';
                }

                return $data['convictionDate'];
            },
            'name' => 'convictionDate'
        ),
        array(
            'title' => 'Date of offence',
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Name / defendant type',
            'formatter' => function ($data) {
                return $data['name'] . '<br />' . $data['defendantType'];
            },
            'name' => 'name'
        ),
        array(
            'title' => 'Description',
            'name' => 'categoryText'
        ),
        array(
            'title' => 'Court/FPN',
            'name' => 'court'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty'
        ),
        array(
            'title' => 'SI',
            'name' => 'msi'
        ),
        array(
            'title' => 'Declared',
            'name' => 'isDeclared'
        ),
        array(
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
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
