<?php

return array(
    'variables' => array(
        'title' => 'Vehicles'
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
            'title' => 'Select',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'VRM',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'edit'
                    ),
                    'selfserve/vehicle-safety/vehicle'
                ) . '">' . $row['vrm'] . '</a>';
            }
        ),
        array(
            'title' => 'Gross plated weight (kg)',
            'format' => '{{platedWeight}} Kg'
        )
    )
);
