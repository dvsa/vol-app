<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.responsibilities.table.licences'
    ),
    'columns' => array(
        array(
            'title' => 'Manager type',
            'name' => 'tmType',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit-tm-licence'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '">' .
                ((isset($row['tmType']['description']) && $row['tmType']['description']) ?
                    $row['tmType']['description'] : 'Not set') . '</a>';
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
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-tm-licence][%d]'
        ),
    )
);
