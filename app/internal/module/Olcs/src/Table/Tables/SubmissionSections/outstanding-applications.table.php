<?php
return array(
    'variables' => array(
        'id' => 'outstanding-applications',
        'title' => 'Outstanding applications',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'outstanding-applications']
        ],
        'title' => 'Outstanding applications'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'outstanding-applications',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'outstanding-applications'
    ),
    'columns' => array(
        array(
            'title' => 'Application No',
            'name' => 'id'
        ),
        array(
            'title' => 'Application type',
            'name' => 'applicationType',
        ),
        array(
            'title' => 'Received date',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'OOO/OOR',
            'formatter' => function ($data, $column) {
                $string = ' - ';
                if (isset($data['ooo'])) {
                    $string = $data['ooo'] . $string;
                }
                if (isset($data['oor'])) {
                    $string .= $data['oor'];
                }
                return $string;
            }
        ),
        array(
            'type' => 'Checkbox',
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        )
    )
);
