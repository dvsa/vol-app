<?php

return array(
    'variables' => array(
        'title' => 'Data retention export'
    ),
    'settings' => array(
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Description',
            'formatter' => function ($row) {
                return sprintf(
                    '%s %s [%s] [%s]',
                    $row['organisationName'],
                    $row['licNo'],
                    $row['entityName'],
                    $row['entityPk']
                );
            },
        ),
        array(
            'title' => 'Deleted date',
            'formatter' => 'Date',
            'name' => 'deletedDate',
        ),
    )
);
