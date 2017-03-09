<?php
return array(
    'variables' => array(
        'id' => 'oppositions',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'oppositions']
        ],
        'title' => 'Oppositions'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'oppositions',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'oppositions'
    ),
    'columns' => array(
        array(
            'title' => 'Opposition type',
            'formatter' => function ($data) {
                return $data['oppositionType'];
            },
        ),
        array(
            'title' => 'Date received',
            'name' => 'dateReceived',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'opposition' => $data['id']),
                    'case_opposition',
                    true
                ) . '">' . $data['dateReceived'] . '</a>';
            },
        ),
        array(
            'title' => 'Contact name',
            'formatter' => function ($data) {
                return $data['contactName']['forename'] . ' ' . $data['contactName']['familyName'];
            }
        ),
        array(
            'title' => 'Grounds',
            'formatter' => function ($data) {
                return implode(', ', $data['grounds']);
            }
        ),
        array(
            'title' => 'Valid',
            'name' => 'isValid'
        ),
        array(
            'title' => 'Copied',
            'name' => 'isCopied'
        ),
        array(
            'title' => 'In time',
            'name' => 'isInTime'
        ),
        array(
            'title' => 'Willing to attend PI',
            'name' => 'isWillingToAttendPi',
        ),
        array(
            'title' => 'Public Inquiry',
            'name' => 'isPublicInquiry'
        ),
        array(
            'title' => 'Withdrawn',
            'name' => 'isWithdrawn'
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
