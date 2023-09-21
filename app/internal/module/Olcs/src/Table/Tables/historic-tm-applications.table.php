<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;

return array(
    'variables' => array(
        'titleSingular' => 'Application',
        'title' => 'Applications'
    ),
    'columns' => array(
        array(
            'title' => 'Licence No',
            'name' => 'licNo'
        ),
        array(
            'title' => 'Application id',
            'name' => 'applicationId'
        ),
        array(
            'title' => 'Date added',
            'formatter' => Date::class,
            'name' => 'dateAdded'
        ),
        array(
            'title' => 'Date removed',
            'formatter' => Date::class,
            'name' => 'dateRemoved'
        ),
        array(
            'title' => 'Seen qualification?',
            'formatter' => YesNo::class,
            'name' => 'seenQualification'
        ),
        array(
            'title' => 'Seen contract?',
            'formatter' => YesNo::class,
            'name' => 'seenContract'
        ),
        array(
            'title' => 'Weekly hours of work',
            'isNumeric' => true,
            'name' => 'hoursPerWeek'
        )
    )
);
