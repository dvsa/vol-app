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
                'options' => array(25, 50, 100)
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
            'sort' => 'description'
        ),
        array(
            'title' => 'Retention period',
            'name' => 'retentionPeriod',
            'sort' => 'retentionPeriod',
        ),
        array(
            'title' => 'Max data set',
            'name' => 'maxDataSet',
            'sort' => 'maxDataSet',
        ),
        array(
            'title' => 'is Enabled',
            'name' => 'isEnabled',
            'sort' => 'isEnabled',
            'formatter' => 'DataRetentionRuleIsEnabled'
        ),
        array(
            'title' => 'Action type',
            'name' => 'actionType',
            'sort' => 'actionType',
            'formatter' => 'DataRetentionRuleActionType'
        ),
    )
);
