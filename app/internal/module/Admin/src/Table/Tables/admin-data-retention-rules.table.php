<?php

return array(
    'variables' => array(
        'title' => 'Data Retention rules',
        'titleSingular' => 'Data retention rule',
        'titlePlural' => 'Data retention rules',
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
            'title' => 'ID',
            'name' => 'id',
            'sort' => 'id',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => 'DataRetentionRuleLink'
        ),
    )
);
