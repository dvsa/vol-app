<?php

return array(
    'variables' => array(
        'title' => 'Environmental complaints',
    ),
    'settings' => array(),
    'columns' => array(
        array(
            'title' => 'Case No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('case' => $row['case']['id'], 'tab' => 'overview'),
                    'case_opposition',
                    false
                ) . '">' . $row['case']['id'] . '</a>';
            }
        ),
        array(
            'title' => 'Date received',
            'formatter' => 'Date',
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => 'Name',
            'name' => 'complainantContactDetails->person',
        ),
        array(
            'title' => 'OC Address',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Address';
                $addressList = '';
                foreach ($data['operatingCentres'] as $operatingCentre) {
                    $addressList .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
                }

                return $addressList;
            },
            'name' => 'operatingCentres'
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['status']['description'];
            }
        )
    )
);
