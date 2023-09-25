<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Name;

return array(
    'variables' => array(
        'title' => 'Environmental complaints',
        'titleSingular' => 'Environmental complaint',
    ),
    'settings' => array(),
    'columns' => array(
        array(
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => function ($row) {
                return '<a class="govuk-link" href="' . $this->generateUrl(
                    array('case' => $row['case']['id'], 'tab' => 'overview'),
                    'case_opposition',
                    false
                ) . '">' . $row['case']['id'] . '</a>';
            }
        ),
        array(
            'title' => 'Date received',
            'formatter' => Date::class,
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => Name::class,
            'name' => 'complainantContactDetails->person',
        ),
        array(
            'title' => 'OC Address',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Address::class;
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
