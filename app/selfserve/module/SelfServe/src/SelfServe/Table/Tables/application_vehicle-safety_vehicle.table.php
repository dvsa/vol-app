<?php

return array(
    'variables' => array(
        'title' => 'application_vehicle-safety_vehicle.table.title'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'application_vehicle-safety_vehicle.table.vrm',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'edit'
                    ),
                    'Application/VehicleSafety/Vehicle'
                ) . '">' . $row['vrm'] . '</a>';
            }
        ),
        array(
            'title' => 'application_vehicle-safety_vehicle.table.weight',
            'format' => '{{platedWeight}} Kg'
        )
    )
);
