<?php

return array(
    'variables' => array(
        'title' => 'Not Pi',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'NonPi',
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Meeting date',
            'formatter' => function ($data, $column) {
                $url = $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'case_non_pi', true
                );
                $column['formatter'] = 'Date';
                return '<a href="' . $url . '">' . date(\DATETIMESEC_FORMAT, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Meeting venue',
            'formatter' => function ($data) {
                return (isset($data['venue']['name']) ? $data['venue']['name'] : $data['venueOther']);
            }
        ),
        array(
            'title' => 'Witness Count',
            'name' => 'witnessCount'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
