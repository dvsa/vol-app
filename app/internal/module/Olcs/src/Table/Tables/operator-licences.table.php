<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\RefData;

return array(
    'variables' => array(
        'titleSingluar' => 'Licence',
        'title' => 'Licences'
    ),
    'settings' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Licence No.',
            'formatter' => function ($row) {
                return '<a class="govuk-link" href="' . $this->generateUrl(
                    array('licence' => $row['id']),
                    'licence'
                ) . '">' . $row['licNo'] . '</a>';
            }
        ),
        array(
            'title' => 'Type',
            'formatter' => LicenceTypeShort::class,
        ),
        array(
            'title' => 'Start date',
            'formatter' => Date::class,
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Status',
            'formatter' => RefData::class,
            'name' => 'status'
        ),
    )
);
