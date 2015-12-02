<?php

return array(
    'variables' => array(
        'id' => 'conditions',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conditions-and-undertakings', 'subSection' => 'conditions']
        ],
        'title' => 'Conditions'
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
            'width' => '10%',
            'name' => 'id'
        ),
        array(
            'title' => 'Added via',
            'width' => '10%',
            'formatter' => function ($data, $column, $sl) {
                $string = $sl->get('translator')->translate($data['addedVia']) . ' '
                    .$data['parentId'];
                return $string;
            },
        ),
        array(
            'title' => 'Fulfilled',
            'width' => '10%',
            'formatter' => function ($data, $column) {
                return $data['isFulfilled'] == 'Y' ? 'Yes' : 'No';
            },
        ),
        array(
            'title' => 'Status',
            'width' => '10%',
            'formatter' => function ($data, $column) {
                return $data['isDraft'] == 'Y' ? 'Draft' : 'Approved';
            },
        ),
        array(
            'title' => 'Attached to',
            'width' => '10%',
            'formatter' => function ($data, $column, $sm) {
                $attachedTo = $data['attachedTo'] == 'Operating Centre' ? 'OC' : $data['attachedTo'];
                return $sm->get('translator')->translate($attachedTo);
            }
        ),
        array(
            'title' => 'OC Address',
            'width' => '25%',
            'formatter' => 'Address',
            'name' => 'OcAddress'
        ),
        array(
            'title' => 'Notes',
            'width' => '25%',
            'name' => 'notes',
            'formatter' => 'Comment',
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
