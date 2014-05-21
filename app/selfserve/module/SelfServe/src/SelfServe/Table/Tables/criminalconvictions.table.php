<?php

return array(
    'variables' => array(
        'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-tableHeader'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnName',
            'name' => 'name',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnDate',
            'name' => 'dateOfConviction',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnOffence',
            'name' => 'convictionNotes',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnNameOfCourt',
            'name' => 'courtFpm',
        ),
        array(
            'title' => 'selfserve-app-subSection-previous-history-criminal-conviction-columnPenalty',
            'name' => 'penalty',
        )
    ),
    // Footer configuration
    'footer' => array(
    )
);
