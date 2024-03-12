<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'titleSingluar' => 'Licence',
        'title' => 'Licences'
    ],
    'settings' => [
    ],
    'columns' => [
        [
            'title' => 'Licence No.',
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['licence' => $row['id']],
                'licence'
            ) . '">' . $row['licNo'] . '</a>'
        ],
        [
            'title' => 'Type',
            'formatter' => LicenceTypeShort::class,
        ],
        [
            'title' => 'Start date',
            'formatter' => Date::class,
            'name' => 'inForceDate'
        ],
        [
            'title' => 'Status',
            'formatter' => RefData::class,
            'name' => 'status'
        ],
    ]
];
