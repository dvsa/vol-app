<?php

use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\Formatter\SeriousInfringementLink;
use Common\Service\Table\Formatter\YesNo;

return array(
    'variables' => array(
        'titleSingular' => 'Serious Infringement',
        'title' => 'Serious Infringements'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'ID',
            'isNumeric' => true,
            'name' => 'id',
            'formatter' => SeriousInfringementLink::class
        ),
        array(
            'title' => 'Category',
            'formatter' => RefData::class,
            'name' => 'siCategoryType'
        ),
        array(
            'title' => 'Penalty applied',
            'formatter' => YesNo::class,
            'name' => 'responseSet'
        ),
    )
);
