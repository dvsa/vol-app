<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'titleSingular' => 'Application',
        'title' => 'Applications'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Licence/App No.',
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['application' => $row['id']],
                'lva-application'
            ) . '">' . $row['licence']['licNo'] .'/'. $row['id'] . '</a>'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($row) => $row['isVariation'] ? 'Variation' : 'New'
        ],
        [
            'title' => 'Received',
            'formatter' => Date::class,
            'name' => 'receivedDate'
        ],
        [
            'title' => 'Status',
            'formatter' => RefData::class,
            'name' => 'status'
        ],
    ]
];
