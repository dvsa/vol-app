<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\PublicationNumber;

return [
    'variables' => [
        'titleSingular' => 'Publications',
        'title' => 'Publications'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'generate' => ['requireRows' => true, 'class' => 'govuk-button js-require--one'],
                'publish' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Traffic Area',
            'name' => 'trafficArea',
            'formatter' => fn($row) => $row['trafficArea']['name']
        ],
        [
            'title' => 'Publication No.',
            'isNumeric' => true,
            'formatter' => PublicationNumber::class,
            'name' => 'publicationNo',
            'sort' => 'publicationNo',
        ],
        [
            'title' => 'Document Type',
            'name' => 'pubType',
        ],
        [
            'title' => 'Document status',
            'formatter' => fn($data) => $data['pubStatus']['description']
        ],
        [
            'title' => 'Publication date',
            'name' => 'pubDate',
            'sort' => 'pubDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
