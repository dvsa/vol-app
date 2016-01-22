<?php

return array(
    'variables' => array(
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
            'formatter' => 'Date',
            'name' => 'dateAdded'
        ),
        array(
            'title' => 'Date removed',
            'formatter' => 'Date',
            'name' => 'dateRemoved'
        ),
        array(
            'title' => 'Seen qualification?',
            'formatter' => 'YesNo',
            'name' => 'seenQualification'
        ),
        array(
            'title' => 'Seen contract?',
            'formatter' => 'YesNo',
            'name' => 'seenContract'
        ),
        array(
            'title' => 'Weekly hours of work',
            'name' => 'hoursPerWeek'
        )
    )
);
