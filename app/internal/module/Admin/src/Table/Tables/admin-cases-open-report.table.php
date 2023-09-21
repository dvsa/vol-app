<?php

use Common\Service\Table\Formatter\CaseEntityName;
use Common\Service\Table\Formatter\CaseEntityNrStatus;
use Common\Service\Table\Formatter\CaseLink;
use Common\Service\Table\Formatter\CaseTrafficArea;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'titleSingular' => 'Open case',
        'title' => 'Open cases',
        'empty_message' => 'Open cases are not found by specified filter criteria',
    ],
    'settings' => [
        'crud' => [
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Case Id',
            'isNumeric' => true,
            'formatter' => CaseLink::class,
            'name' => 'id',
        ],
        [
            'title' => 'Entity',
            'formatter' => CaseEntityNrStatus::class,
        ],
        [
            'title' => 'Traffic area',
            'formatter' => CaseTrafficArea::class,
        ],
        [
            'title' => 'Name',
            'formatter' => CaseEntityName::class,
        ],
        [
            'title' => 'Open Date',
            'formatter' => Date::class,
            'name' => 'openDate',
        ],
        [
            'title' => 'Type',
            'formatter' => RefData::class,
            'name' => 'caseType',
        ],
        [
            'title' => 'Category',
            'formatter' => RefData::class,
            'name' => 'categorys',
            'separator' => ', ',
        ],
        [
            'title' => 'Outcome',
            'formatter' => RefData::class,
            'name' => 'outcomes',
            'separator' => ', ',
        ],
    ],
];
