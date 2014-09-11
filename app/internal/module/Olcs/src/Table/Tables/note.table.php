<?php

return array(
    'variables' => array(
        'title' => 'Notes'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
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
            'title' => 'Created',
            'formatter' => function ($data) {
                $routeParams = array('action' => 'edit', 'id' => $data['id']);

                switch ($data['noteType']['id']) {
                    case 'note_t_bus':
                        $route = 'licence/bus-processing/modify-note';
                        $routeParams['busRegId'] = $data['busReg']['id'];
                        break;
                    default:
                        $route = 'licence/processing/modify-note';
                }

                return '<a href="' . $this->generateUrl(
                    $routeParams,
                    $route,
                    true
                ) . '">' . (new \DateTime($data['createdOn']))->format('d/m/Y') . '</a>';
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Author',
            'formatter' => function ($data) {
                return $data['createdBy']['name'];
            }
        ),
        array(
            'title' => 'Note',
            'name' => 'comment',
            'sort' => 'comment'
        ),
        array(
            'title' => 'Note Type',
            'formatter' => function ($data) {
                return $data['noteType']['description'];
            },
            'sort' => 'noteType'
        ),
        array(
            'title' => 'Priority',
            'name' => 'priority',
            'sort' => 'priority'
        )
    )
);
