<?php

return array(
    'variables' => array(
        'title' => 'Undertakings'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'undertakings',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'No.',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id'], 'type' => 'undertakings'),
                    'undertakings',
                    true
                ) . '" class="js-modal-ajax">' . $data['id'] . '</a>';
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
    )
);
