<?php
return array(
    'variables' => array(
        'title' => 'Outstanding applications',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'outstanding-applications']
        ],
        'title' => 'Outstanding applications'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'oppositions',
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
                $column['formatter'] = 'Date';
                $string = ' - ';
                if (isset($data['ooo'])) {
                    $ooo = new DateTime($data['ooo']);
                    $string = $ooo->format('d/m/Y') . $string;
                }
                if (isset($data['oor'])) {
                    $oor = new DateTime($data['oor']);
                    $string .= $oor->format('d/m/Y');
                }
                return $string;
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);
