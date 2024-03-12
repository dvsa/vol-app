<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [
        'titleSingular' => 'Application',
        'title' => 'Applications'
    ],
    'columns' => [
        [
            'title' => 'Licence No',
            'name' => 'licNo'
        ],
        [
            'title' => 'Application id',
            'name' => 'applicationId'
        ],
        [
            'title' => 'Date added',
            'formatter' => Date::class,
            'name' => 'dateAdded'
        ],
        [
            'title' => 'Date removed',
            'formatter' => Date::class,
            'name' => 'dateRemoved'
        ],
        [
            'title' => 'Seen qualification?',
            'formatter' => YesNo::class,
            'name' => 'seenQualification'
        ],
        [
            'title' => 'Seen contract?',
            'formatter' => YesNo::class,
            'name' => 'seenContract'
        ],
        [
            'title' => 'Weekly hours of work',
            'isNumeric' => true,
            'name' => 'hoursPerWeek'
        ]
    ]
];
