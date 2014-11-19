<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conditions-and-undertakings']
        ],
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'undertakings',
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
                    array('action' => 'edit', 'id' => $data['id'], 'type' => 'undertakings'),
                    'undertakings',
                    true
                ) . '">' . $data['id'] . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Added via',
            'formatter' => function ($data, $column) {
                return 'Case ' . $data['caseId'];
            },
            'name' => 'caseId'
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
                return $sm->get('translator')->translate($data['attachedTo']['id']);
            }
        ),
        array(
            'title' => 'S4',
            'formatter' => function ($data, $column) {
                return 'ToDo'; // todo S4 clarification
            }
        ),
        array(
            'title' => 'OC Address',
            'width' => '350px',
            'formatter' => 'Address',
            'name' => 'operatingCentreAddress'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
