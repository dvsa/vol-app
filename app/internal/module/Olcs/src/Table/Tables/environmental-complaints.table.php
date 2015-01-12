<?php

return array(
    'variables' => array(
        'title' => 'Environmental complaints',
        'action_route' => [
            'route' => 'case_environmental_complaint',
            'params' => []
        ],
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
                'default' => 100,
                'options' => array(100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'complaint' => $data['id']),
                    'case_complaint',
                    true
                ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => function ($data, $column) {
                return $data['complainantContactDetails']['forename'] . ' ' .
                $data['complainantContactDetails']['familyName'];
            }
        ),
        array(
            'title' => 'OC Address',
            'width' => '350px',
            'formatter' => function ($data, $column) {

                $column['formatter'] = 'Address';
                $addressList = '';
                foreach ($data['ocComplaints'] as $ocComplaint) {
                    $addressList .= $this->callFormatter($column, $ocComplaint['operatingCentre']['address']) . '<br
                    />';
                }

                return $addressList;
            },
            'name' => 'ocComplaints'
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
