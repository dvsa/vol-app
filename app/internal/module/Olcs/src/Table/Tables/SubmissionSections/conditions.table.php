<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conditions']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conditions',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'conditions'
    ),
    'columns' => array(
        array(
            'title' => 'No.',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    'case_conditions_undertakings',
                    true
                ) . '">' . $data['id'] . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column, $sl) {
                return $sl->get('translator')->translate($data['conditionType']['description']);
            },
        ),
        array(
            'title' => 'Added via',
            'formatter' => function ($data, $column, $sl) {
                return $sl->get('translator')->translate($data['addedVia']['description']);
            },
        ),
        array(
            'title' => 'Fulfilled',
            'formatter' => function ($data, $column) {
                return $data['isFulfilled'] == 'Y' ? 'Yes' : 'No';
            },
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['isDraft'] == 'Y' ? 'Draft' : 'Approved';
            },
        ),
        array(
            'title' => 'Attached to',
            'formatter' => function ($data, $column, $sm) {
                return $sm->get('translator')->translate($data['attachedTo']['description']);
            },
        ),
        array(
            'title' => 'S4',
            'formatter' => function ($data, $column) {
                return 'ToDo';
            }
        ),
        array(
            'title' => 'OC Address',
            'width' => '300px',
            'formatter' => function ($data, $column, $sm) {
                if (isset($data['operatingCentre']['address'])) {
                    $column['formatter'] = 'Address';
                    return $this->callFormatter($column, $data['operatingCentre']['address']);
                }
                return 'N/a';
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
