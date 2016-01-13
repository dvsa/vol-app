<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.responsibilities.table.licences'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'edit-tm-licence' => array(
                    'label' => 'Edit',
                    'class' => 'secondary js-require--one',
                    'requireRows' => true
                ),
                'delete-tm-licence' => array(
                    'label' => 'Remove',
                    'class' => 'secondary js-require--multiple',
                    'requireRows' => true
                )
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Manager Type',
            'name' => 'tmType',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit-tm-licence'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '">' . $row['tmType']['description'] . '</a>';
            },
        ),
        array(
            'title' => 'No. of operating centres',
            'name' => 'ocCount',
            'formatter' => function ($row) {
                return count($row['operatingCentres']);
            }
        ),
        array(
            'title' => 'Licence No',
            'name' => 'licence',
            'formatter' => function ($row) {
                $routeParams = ['licence' => $row['licence']['id']];
                $url = $this->generateUrl($routeParams, 'lva-licence/transport_managers');
                return '<a href="'. $url . '">' . $row['licence']['licNo'] . '</a>';
            },
        ),
        array(
            'title' => 'Operator name',
            'name' => 'operatorName',
            'formatter' => function ($row) {
                return $row['licence']['organisation']['name'];
            },
        ),
        array(
            'title' => 'Hours per week',
            'name' => 'hours',
            'formatter' => 'SumColumns',
            'columns' => ['hoursMon', 'hoursTue', 'hoursWed', 'hoursThu', 'hoursFri', 'hoursSat', 'hoursSun']
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
