<?php

return array(
    'variables' => array(
        'title' => 'Environmental complaints',
        'titleSingular' => 'Environmental complaint',
        'action_route' => [
            'route' => 'case_environmental_complaint',
            'params' => []
        ],
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'generate' => array(
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one')
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Date received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'complaint' => $data['id']),
                    'case_environmental_complaint',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => function ($data, $column) {
                return $data['complainantContactDetails']['person']['forename'] . ' ' .
                $data['complainantContactDetails']['person']['familyName'];
            }
        ),
        array(
            'title' => 'OC Address',
            'width' => '350px',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Address';
                $addressList = '';
                if (!empty($data['operatingCentres'])) {
                    foreach ($data['operatingCentres'] as $operatingCentre) {
                        $addressList
                            .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
                    }
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
