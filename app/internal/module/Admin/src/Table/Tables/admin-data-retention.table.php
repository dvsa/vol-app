<?php

return array(
    'variables' => array(
        'title' => 'Data Retention Rules',
        'titleSingular' => 'Data Retention'
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
            'title' => 'Key',
            'name' => 'ID',
            'sort' => 'id',
            'formatter' => 'SystemParameterLink'
        ),
        array(
            'title' => 'Rule',
            'name' => 'paramValue',
            'sort' => 'paramValue',
        ),
    )
);
