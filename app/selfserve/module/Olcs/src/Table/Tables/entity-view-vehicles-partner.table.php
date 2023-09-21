<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValueReplacer;
use Common\Service\Table\Formatter\YesNo;

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array('id' => 'vehicles'),
    'columns' => array(
        array(
            'title' => 'Interim',
            'name' => 'interimApplication',
            'formatter' => YesNo::class,
        ),
        array(
            'title' => 'Vehicle registration number',
            'name' => 'vrm',
            'formatter' => function ($data) {
                if (!is_null($data['interimApplication'])) {
                    return $data['vehicle']['vrm'] . ' (interim)';
                }

                return $data['vehicle']['vrm'];
            }
        ),
        array(
            'title' => 'Plated weight',
            'isNumeric' => true,
            'stringFormat' => '{vehicle->platedWeight} Kg',
            'formatter' => StackValueReplacer::class
        ),
        array(
            'title' => 'Specified date',
            'formatter' => Date::class,
            'name' => 'specifiedDate'
        ),
    )
);
