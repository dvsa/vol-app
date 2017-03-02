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
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
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
            'formatter' => function ($data) {
                $string = $data['id'];
                if (isset($data['licNo'])) {
                    $string = $data['licNo'] . ' / ' . $string;
                }

                return $string;
            }
        ),
        array(
            'title' => 'Application type',
            'name' => 'applicationType'
        ),
        array(
            'title' => 'Received date',
            'name' => 'receivedDate'
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
