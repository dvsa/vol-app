<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.responsibilities.table.applications'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('label' => 'Add', 'class' => 'action--primary'),
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Manager Type',
            'name' => 'tmType',
            'formatter' => 'TmApplicationManagerType'
        ),
        array(
            'title' => 'No. of operating centres',
            'name' => 'ocCount',
            'formatter' => function ($row) {
                return count($row['operatingCentres']);
            }
        ),
        array(
            'title' => 'Application ID',
            'name' => 'application',
            'formatter' => function ($row) {
                $routeParams = ['application' => $row['application']['id']];
                $route = $row['application']['isVariation'] ?
                    'lva-variation/transport_managers' : 'lva-application/transport_managers';
                $url = $this->generateUrl($routeParams, $route);
                return '<a href="'. $url . '">' .
                    $row['application']['licence']['licNo'] . '/' . $row['application']['id'] .
                    '</a>';
            },
        ),
        array(
            'title' => 'Operator name',
            'name' => 'operatorName',
            'formatter' => function ($row) {
                return $row['application']['licence']['organisation']['name'];
            },
        ),
        array(
            'title' => 'Hours per week',
            'name' => 'hours',
            'formatter' => 'SumColumns',
            'columns' => ['hoursMon', 'hoursTue', 'hoursWed', 'hoursThu', 'hoursFri', 'hoursSat', 'hoursSun']
        ),
        array(
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-tm-application][%d]'
        ),
    )
);
