<?php

return array(
    'variables' => array(
        'title' => 'Hearings'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true)
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
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Hearing Date',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'case_pi', true);

                $column['formatter'] = 'Date';
                return '<a href="' . $url . '">' . date('d/m/Y', strtotime($data['dateOfHearing'])) . '</a>';
            },
            'name' => 'id'
        ),
        array(
            'title' => 'Is Adjourned',
            'name' => 'isAdjourned'
        ),
        array(
            'title' => 'Venue',
            'name' => 'venue'
        ),
        array(
            'title' => 'Presiding TC',
            'formatter' => function ($data) {
                return $data['presidingTc']['name'];
            }
        ),
    )
);
