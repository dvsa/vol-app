<?php

$translationPrefix = 'internal-operator-unlicensed-vehicles.table';

return array(
    'variables' => array(
        'title' => 'Vehicles',
        'titleSingular' => 'Vehicle',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        ),
        'useQuery' => true
    ),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.vrm',
            'stack' => 'vehicle->vrm',
            'formatter' => 'StackValue',
            'action' => 'edit',
            'type' => 'Action',
        ),
        array(
            'title' => $translationPrefix . '.weight',
            'stack' => 'vehicle->platedWeight',
            'formatter' => 'UnlicensedVehicleWeight',
            'name' => 'weight',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
