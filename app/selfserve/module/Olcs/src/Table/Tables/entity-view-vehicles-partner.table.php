<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array('id' => 'vehicles'),
    'columns' => array(
        array(
            'title' => 'Interim',
            'name' => 'interimApplication',
            'formatter' => 'yesno',
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
            'stringFormat' => '{vehicle->platedWeight} Kg',
            'formatter' => 'StackValueReplacer'
        ),
        array(
            'title' => 'Specified date',
            'formatter' => 'Date',
            'name' => 'specifiedDate'
        ),
    )
);
