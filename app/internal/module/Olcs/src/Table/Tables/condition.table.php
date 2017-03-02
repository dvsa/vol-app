<?php

return array(
    'variables' => array(
        'title' => 'Conditions & undertakings',
        'empty_message' => 'There are no conditions or undertakings'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conditions',
            'actions' => array(
                'add' => array('class' => 'action--primary', 'label' => 'Add condition or undertaking'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
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
                    array('action' => 'edit', 'id' => $data['id']),
                    'case_conditions_undertakings',
                    true
                ) . '" class="js-modal-ajax">' . $data['id'] . '</a>';
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
            'title' => 'OC address',
            'width' => '300px',
            'formatter' => function ($data, $column, $sm) {

                if (isset($data['operatingCentre']['address'])) {

                    $column['formatter'] = 'Address';

                    return $this->callFormatter($column, $data['operatingCentre']['address']);
                }

                return 'N/a';
            }
        ),
    )
);
