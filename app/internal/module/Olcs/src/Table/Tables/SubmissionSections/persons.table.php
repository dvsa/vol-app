<?php

return array(
    'settings' => array(
        'submission_section' => 'display'
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Title',
            'name' => 'title'
        ),
        array(
            'title' => 'Firstname',
            'name' => 'forename'
        ),
        array(
            'title' => 'Surname',
            'name' => 'familyName'
        ),
        array(
            'title' => 'DOB',
            'formatter' => 'Date',
            'name' => 'birthDate'
        )
    )
);
