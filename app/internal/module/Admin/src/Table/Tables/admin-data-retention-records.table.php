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
                'assign' => array(
                    'label' => 'Assign',
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
                ),
                'delay' => array(
                    'label' => 'Delay',
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
                ),
                'review' => array(
                    'label' => 'Mark for Review',
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
                ),
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
            'title' => 'Assigned to',
            'formatter' => 'DataRetentionAssignedTo',
        ),
        array(
            'title' => 'Select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    ),
);
