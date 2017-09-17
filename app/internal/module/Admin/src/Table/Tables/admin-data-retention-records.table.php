<?php

return array(
    'variables' => array(
        'title' => 'Data Retention Records',
        'titleSingular' => 'Data retention record',
        'titlePlural' => 'Data retention records',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'delete' => array(
                    'label' => 'Mark as Delete',
                    'requireRows' => true,
                    'class' => 'action--delete js-require--multiple'
                ),
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'formatter' => 'DataRetentionRecordLink',
        ),
        array(
            'title' => 'Date added',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Next review date',
            'name' => 'nextReviewDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'formatter' => 'DataRetentionRecordCheckbox',
        ),
    )
);
