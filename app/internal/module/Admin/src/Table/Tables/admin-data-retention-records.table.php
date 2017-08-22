<?php

return array(
    'variables' => array(
        'title' => 'Data Retention Records',
        'titleSingular' => 'Data retention record',
        'titlePlural' => 'Data retention records',
    ),
    'settings' => array(
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
            'name' => 'organisationName',
        ),
        array(
            'title' => 'Added',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Delay until',
            'name' => 'deletedDate',
            'formatter' => 'Date',
        ),
    )
);
