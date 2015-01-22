<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.responsibilities.table.applications'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('label' => 'Add', 'class' => 'primary'),
                'edit-tm-application' => array('label' => 'Edt', 'class' => 'secondary', 'requireRows' => true),
                'print' => array('label' => 'Print', 'class' => 'secondary', 'requireRows' => true),
                'delete-tm-application' => array('label' => 'Remove', 'class' => 'secondary', 'requireRows' => true)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Manager Type',
            'name' => 'tmType',
            'formatter' => function ($row) {
                return '<a href="" class=js-modal-ajax>' . $row['tmType']['description'] . '</a>';
            },
        ),
        array(
            'title' => 'No. of operating centres',
            'name' => 'ocCount',
        ),
        array(
            'title' => 'Application ID',
            'name' => 'application',
            'formatter' => function ($row) {
                $routeParams = ['application' => $row['application']['id']];
                $url = $this->generateUrl($routeParams, 'lva-application/transport_managers');
                return '<a href="'. $url . '">' . $row['application']['id'] . '</a>';
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
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
