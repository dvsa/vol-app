<?php

return array(
    'variables' => array(
        'title' => 'Convictions'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'conviction',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                /* 'dealt' => array('class' => 'secondary', 'requireRows' => true, 'label' => 'Mark as Dealt With'), */
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--one')
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
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date of conviction',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'conviction' => $data['id']], 'conviction', true);
                $class = 'js-modal-ajax';
                if ($data['convictionDate'] == null) {
                    return '<a href="' . $url . '" class="' . $class . '">N/A</a>';
                }

                $column['formatter'] = 'Date';
                return '<a href="' . $url . '" class="' . $class . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'convictionDate'
        ),
        array(
            'title' => 'Date of offence',
            'formatter' => 'Date',
            'name' => 'offenceDate'
        ),
        array(
            'title' => 'Name / defendant type',
            'formatter' => function ($data, $column, $sm) {

                $translator = $sm->get('translator');

                $person = $data['personFirstname'] . ' ' . $data['personLastname'];
                $organisationName = $data['operatorName'];
                $name = ($organisationName == '' ? $person : $organisationName) . ' <br /> '
                      . $translator->translate($data['defendantType']['description']);

                return $name;
            }
        ),
        array(
            'title' => 'Description',
            'formatter' => function ($row) {
                /*
                    conv_c_cat_1144 is ref data id for a 'User defined' category.
                    However all new convictions with User defined descriptions have no category.
                    Hence the logic below.
                */
                if (count($row['convictionCategory']) && $row['convictionCategory']['id'] != 'conv_c_cat_1144') {
                    $row['categoryText'] = $row['convictionCategory']['description'];
                }

                $categoryText = $row['categoryText'];

                $append = strlen($categoryText) > 30 ? '...' : '';
                return nl2br(substr($categoryText, 0, 30)) . $append;
            }
        ),
        array(
            'title' => 'Court/FPN',
            'name' => 'court'
        ),
        array(
            'title' => 'Penalty',
            'name' => 'penalty'
        ),
        array(
            'title' => 'SI',
            'name' => 'msi'
        ),
        array(
            'title' => 'Declared',
            'name' => 'isDeclared'
        ),
        array(
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
        )
    )
);
