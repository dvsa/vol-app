<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.otherlicences.table',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'other-licence-licences-add' => array('label' => 'Add', 'class' => 'primary'),
                'edit-other-licence-licences' => array(
                    'label' => 'Edit',
                    'class' => 'secondary js-require--one',
                    'requireRows' => true
                ),
                'delete-other-licence-licences' => array(
                    'label' => 'Remove',
                    'class' => 'secondary js-require--multiple',
                    'requireRows' => true
                )
            ),
        ),
    ),
    'columns' => array(
        array(
            'title' => 'internal.transport-manager.otherlicences.table.lic_no',
            'name' => 'licNo',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit-other-licence-licences'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '" class=js-modal-ajax>' . $row['licNo'] . '</a>';
            },
        ),
        array(
            'title' => 'internal.transport-manager.otherlicences.table.role',
            'name' => 'role',
            'formatter' => 'RefData'
        ),
        array(
            'title' => 'internal.transport-manager.otherlicences.table.operating_centres',
            'name' => 'operatingCentres',
        ),
        array(
            'title' => 'internal.transport-manager.otherlicences.table.total_auth_vehicles',
            'name' => 'totalAuthVehicles',
        ),
        array(
            'title' => 'internal.transport-manager.otherlicences.table.hours_per_week',
            'name' => 'hoursPerWeek',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
