<?php

return array(
    'variables' => array(
        'title' => 'Undertakings'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'No.',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Added via',
            'name' => 'caseId'
        ),
        array(
            'title' => 'Fulfilled',
            'formatter' => function ($data, $column) {
                return $data['isFulfilled'] ? 'Yes' : 'No';
            },
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['isDraft'] ? 'Draft' : 'Approved';
            },
        ),
        array(
            'title' => 'Attached to',
            'name' => 'attachedTo'
        ),
        array(
            'title' => 'S4',
            'formatter' => function ($data, $column) {
                return 'ToDo'; // todo S4 clarification
            }
        ),
        array(
            'title' => 'OC Address',
            'formatter' => function ($data, $column) {
                return 'ToDo'; // todo S4 clarification
            }
        ),
    )
);
