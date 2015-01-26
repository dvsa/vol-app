<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.responsibilities.table.licences'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'edit' => array('label' => 'Edit', 'class' => 'secondary', 'requireRows' => true),
                'delete-row' => array('label' => 'Remove', 'class' => 'secondary', 'requireRows' => true)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Manager Type',
            'name' => 'tmType',
            'formatter' => function ($row) {
                return '<a href="" class=js-modal-ajax>' . $row['transportManager']['tmType']['description'] . '</a>';
            },
        ),
        array(
            'title' => 'No. of operating centres',
            'name' => 'ocCount',
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
