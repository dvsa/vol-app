<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\TableBuilder;

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
            'formatter' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a class="govuk-link" href="' . $this->generateUrl(
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
