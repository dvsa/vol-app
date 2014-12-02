<?php

return array(
    'variables' => array(
        'title' => 'Bus registrations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'index', 'busRegId' => $data['id']),
                    'licence/bus-details',
                    true
                ) . '">' . $data['regNo'] . '</a>';
            },
            'sort' => 'regNo'
        ),
        array(
            'title' => 'Var No.',
            'name' => 'routeSeq',
            'sort' => 'routeSeq'
        ),
        array(
            'title' => 'Service No.',
            'formatter' => function ($data) {
                $serviceNo = $data['serviceNo'];
                $otherService = [];

                foreach ($data['otherServices'] as $service) {
                    $otherService[] = $service['serviceNo'];
                }

                $otherServiceStr = implode(', ', $otherService);

                if ($serviceNo && $otherServiceStr) {
                    return $serviceNo . ' AND (' . $otherServiceStr . ')';
                } elseif ($serviceNo) {
                    return $serviceNo;
                } elseif ($otherServiceStr) {
                    return $otherServiceStr;
                }

                return '';
            },
            'sort' => 'serviceNo'
        ),
        array(
            'title' => 'Date 1st registered / cancelled',
            'formatter' => function () {
                return 'TBC / TBC';
            },
        ),
        array(
            'title' => 'Starting point',
            'name' => 'startPoint',
            'sort' => 'startPoint'
        ),
        array(
            'title' => 'Finishing point',
            'name' => 'finishPoint',
            'sort' => 'finishPoint'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
