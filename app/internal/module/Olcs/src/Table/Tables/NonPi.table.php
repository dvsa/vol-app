<?php

return array(
    'variables' => array(
        /*'action_route' => [
            'route' => 'case_non_pi',
            'params' => ['action' => 'index']
        ],*/
        'title' => 'Not Pi',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'NonPi',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'useQuery' => true
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
                return '<a href="' . $url . '">' . date('d/m/Y H:i:s', strtotime($data['hearingDate'])) . '</a>';
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
