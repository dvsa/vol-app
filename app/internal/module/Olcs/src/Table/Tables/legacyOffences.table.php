<?php

return array(
    'variables' => array(
        'title' => 'Legacy offences'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'offence',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
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
            'title' => 'Offence date from ',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'conviction', true);

                if ($data['offenceDate'] == null) {
                    return '<a href="' . $url . '">N/A</a>';
                }

                $column['formatter'] = 'Date';
                return '<a href="' . $url . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Originating authority',
            'name' => 'originatingAuthority'
        ),
        array(
            'title' => 'Vehicle',
            'name' => 'vrm'
        ),
        array(
            'title' => 'Trailer',
            'name' => 'isTrailer'
        ),
        array(
            'title' => 'Offence detail',
            'name' => 'notes',
            'formatter' => function ($data, $column) {

                return substr($data['notes'], 0, 150);
            },
        ),
        array(
            'title' => 'Points',
            'name' => 'points'
        )
    )
);
