<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\PublicationNumber;

return [
    'variables' => [
        'title' => 'Published'
    ],
    'settings' => [
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
            'title' => 'Publication date',
            'name' => 'pubDate',
            'sort' => 'pubDate',
            'formatter' => Date::class
        ],
    ]
];
