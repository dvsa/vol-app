<?php

return array(
    'variables' => array(
        'title' => 'GV Permits'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                'reset' => array('requireRows' => true, 'class' => 'secondary js-require--one')
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
            'title' => 'Permit Id',
            'formatter' => function ($data, $column) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    'operator/irfo/gv-permits',
                    true
                ) . '" class="js-modal-ajax">' . $data['id'] . '</a>';
            }
        ),
        array(
            'title' => 'In force date',
            'formatter' => 'Date',
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column) {
                return $data['irfoGvPermitType']['description'];
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['irfoPermitStatus']['description'];
            }
        )
    )
);
