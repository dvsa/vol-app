<?php

return array(
    'variables' => array(
        'title' => 'Notes',
        'titleSingular' => 'Note',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--multiple')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Created',
            'formatter' => function ($data) {
                $routeParams = array('action' => 'edit', 'id' => $data['id']);

                switch ($data['noteType']['id']) {
                    case 'licence/bus-processing':
                        $routeParams['busRegId'] = $data['busReg']['id'];
                        break;
                    case 'case_processing_notes':
                        $routeParams['case'] = $data['case']['id'];
                        break;
                    case 'licence/processing':
                        $routeParams['licence'] = $data['licence']['id'];
                        break;
                    case 'transport-manager/processing':
                        $routeParams['transportManager'] = $data['transportManagerId'];
                        break;
                }

                $url = $this->generateUrl($routeParams, $data['routePrefix'] . '/modify-note', true);

                return '<a class="js-modal-ajax" href="' . $url . '">'
                    . (new \DateTime($data['createdOn']))->format('d/m/Y') . '</a>';
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Author',
            'formatter' => function ($data) {
                return $data['createdBy']['loginId']; //temporary - needs to use person table
            }
        ),
        array(
            'title' => 'Note',
            'formatter' => 'Comment',
            'name' => 'comment',
            'sort' => 'comment'
        ),
        array(
            'title' => 'Note type',
            'formatter' => function ($data) {
                return $data['noteType']['description'];
            },
            'sort' => 'noteType'
        ),
        array(
            'title' => 'Priority',
            'name' => 'priority',
            'sort' => 'priority'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
