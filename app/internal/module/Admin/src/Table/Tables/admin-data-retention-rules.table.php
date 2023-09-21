<?php

use Common\Service\Table\Formatter\DataRetentionRuleActionType;
use Common\Service\Table\Formatter\DataRetentionRuleLink;

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
            'formatter' => DataRetentionRuleLink::class
        ),
        array(
            'title' => 'Action type',
            'name' => 'actionType',
            'sort' => 'actionType',
            'formatter' => DataRetentionRuleActionType::class
        ),
    )
);
