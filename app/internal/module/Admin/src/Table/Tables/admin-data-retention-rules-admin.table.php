<?php

use Common\Service\Table\Formatter\DataRetentionRuleActionType;
use Common\Service\Table\Formatter\DataRetentionRuleAdminLink;
use Common\Service\Table\Formatter\DataRetentionRuleIsEnabled;

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
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => DataRetentionRuleAdminLink::class
        ),
        array(
            'title' => 'Retention period',
            'isNumeric' => true,
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
            'formatter' => DataRetentionRuleIsEnabled::class
        ),
        array(
            'title' => 'Action type',
            'name' => 'actionType',
            'sort' => 'actionType',
            'formatter' => DataRetentionRuleActionType::class
        ),
    )
);
