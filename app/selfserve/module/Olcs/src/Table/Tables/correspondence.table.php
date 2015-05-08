<?php

return array(
    'variables' => array(
        'title' => 'Correspondence',
        'titleSingular' => 'Correspondence',
        'empty_message' => 'dashboard-correspondence-empty-message',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'correspondence',
            'actions' => array()
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Title',
            'name' => 'correspondence',
            'formatter' => 'AccessedCorrespondence'
        ),
        array(
            'title' => 'Licence No.',
            'name' => 'licence',
            'formatter' => 'LicenceNumberLink'
        ),
        array(
            'title' => 'Created',
            'name' => 'date',
            'formatter' => 'Date',
        ),
    )
);
