<?php

return array(
    'variables' => array(
        'title' => 'PSV Authorisations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'reset' => array(
                    'requireRows' => true,
                    'class' => 'action--secondary js-require--one',
                    'label' => 'Reset'
                ),
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
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
            'title' => 'Authorisation Id',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    'operator/irfo/psv-authorisations',
                    true
                ) . '" class="js-modal-ajax">' . $data['id'] . '</a>';
            }
        ),
        array(
            'title' => 'IRFO File Number',
            'name' => 'irfoFileNo'
        ),
        array(
            'title' => 'In force date',
            'formatter' => 'Date',
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column) {
                return $data['irfoPsvAuthType']['description'];
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['status']['description'];
            }
        )
    )
);
