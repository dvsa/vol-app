<?php

return array(
    'variables' => array(
        'title' => 'Printers'
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
            'title' => 'Printer',
            'name' => 'printerName',
            'sort' => 'printerName',
            'formatter' => function ($row) {
                $routeParams = ['printer' => $row['id'], 'action' => 'edit'];
                $route = 'admin-dashboard/admin-printing/admin-printer-management';
                $url = $this->generateUrl($routeParams, $route);
                return '<a href="'. $url . '" class="js-modal-ajax">' . $row['printerName'] .'</a>';
            },
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ),
        array(
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ),
    )
);
