<?php

return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'environmental-complaints']
        ],
        'title' => 'Environmental complaints'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'environmental-complaints',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display'
    ),
    'attributes' => array(
        'name' => 'environmental-complaints'
    ),
    'columns' => array(
        array(
            'title' => 'Date received',
            'name' => 'complaintDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => function ($data, $column) {
                return $data['complainantForename'] . ' ' .
                $data['complainantFamilyName'];
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
            'name' => 'description',
            'formatter' => 'Comment',
            'append' => '...'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column, $sm) {
                $translateService =  $sm->get('translator');
                return empty($data['closeDate']) ?
                    $translateService->translate('Open') : $translateService->translate('Closed');
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
