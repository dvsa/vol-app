<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.previouslicences.table'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'previous-licence-add' => array('label' => 'Add', 'class' => 'primary'),
                'edit-previous-licence' => array('label' => 'Edit', 'class' => 'secondary js-require--one', 'requireRows' => true),
                'delete-previous-licence' => array('label' => 'Remove', 'class' => 'secondary js-require--multiple', 'requireRows' => true)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'internal.transport-manager.previouslicences.table.lic-no',
            'name' => 'licNO',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit-previous-licence'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '" class=js-modal-ajax>' . $row['licNo'] . '</a>';
            },
        ),
        array(
            'title' => 'internal.transport-manager.previouslicences.table.holderName',
            'name' => 'holderName',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
