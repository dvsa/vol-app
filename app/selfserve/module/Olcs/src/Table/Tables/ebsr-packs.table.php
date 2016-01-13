<?php

return array(
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Pack name',
            'name' => 'filename',
            'formatter' => function () {
                return 'pack.zip';
            }
        ),
        array(
            'title' => 'Type',
            'name' => 'ebsrSubmissionType',
            'formatter' => 'RefData'
        ),
        array(
            'title' => 'Submitted',
            'name' => 'submittedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Status',
            'name' => 'ebsrSubmissionStatus',
            'formatter' => 'RefData'
        ),
         array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
