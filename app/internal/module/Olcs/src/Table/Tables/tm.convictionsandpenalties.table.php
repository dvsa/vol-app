<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.convictionsandpenalties.table'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'previous-conviction-add' => array('label' => 'Add', 'class' => 'primary'),
                'edit-previous-conviction' => array('label' => 'Edit', 'class' => 'secondary', 'requireRows' => true),
                'delete-previous-conviction' =>
                    array('label' => 'Remove', 'class' => 'secondary', 'requireRows' => true)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'internal.transport-manager.convictionsandpenalties.table.offence',
            'name' => 'categoryText',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit-previous-conviction'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '" class=js-modal-ajax>' . $row['categoryText'] . '</a>';
            },
        ),
        array(
            'title' => 'internal.transport-manager.convictionsandpenalties.table.conviction-date',
            'name' => 'convictionDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'internal.transport-manager.convictionsandpenalties.table.name-of-court',
            'name' => 'courtFpn',
        ),
        array(
            'title' => 'internal.transport-manager.convictionsandpenalties.table.penalty',
            'name' => 'penalty',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
