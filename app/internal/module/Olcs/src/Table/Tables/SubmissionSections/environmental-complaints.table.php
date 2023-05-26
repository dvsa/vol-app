<?php

return array(
    'variables' => array(
        'id' => 'environmental-complaints',
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
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true)
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
            'name' => 'complaintDate'
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
                foreach ($data['ocAddress'] as $operatingCentre) {
                    $addressList .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
                }

                return $addressList;
            },
            'name' => 'operatingCentres'
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'formatter' => 'Comment',
            'append' => '...'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {

                return empty($data['closeDate']) ?
                    $this->translator->translate('Open') : $this->translator->translate('Closed');
            }
        ),
        array(
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ),
    )
);
