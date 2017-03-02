<?php

return array(
    'variables' => array(
        'title' => 'Teams'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary', 'requireRows' => false),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
            'formatter' => function ($row) {
                $routeParams = ['team' => $row['id'], 'action' => 'edit'];
                $route = 'admin-dashboard/admin-team-management';
                $url = $this->generateUrl($routeParams, $route);
                return '<a href="'. $url . '">' . $row['name'] .'</a>';
            },
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
