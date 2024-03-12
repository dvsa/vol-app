<?php

use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\Formatter\SeriousInfringementLink;
use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [
        'titleSingular' => 'Serious Infringement',
        'title' => 'Serious Infringements'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'ID',
            'isNumeric' => true,
            'name' => 'id',
            'formatter' => SeriousInfringementLink::class
        ],
        [
            'title' => 'Category',
            'formatter' => RefData::class,
            'name' => 'siCategoryType'
        ],
        [
            'title' => 'Penalty applied',
            'formatter' => YesNo::class,
            'name' => 'responseSet'
        ],
    ]
];
