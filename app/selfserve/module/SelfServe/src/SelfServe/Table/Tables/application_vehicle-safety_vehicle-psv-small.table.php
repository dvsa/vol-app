<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv-small.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'vehicle',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'width' => 'checkbox',
            'type' => 'Selector'
        ),
        array(
            'title' => $translationPrefix . '.vrm',
            'name' => 'vrm',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'small-edit'
                    ),
                    'Application/VehicleSafety/VehiclePsv'
                ) . '">' . $row['vrm'] . '</a>';
            }
        ),
        array(
            'title' => $translationPrefix . '.make',
            'name' => 'makeModel'
        ),
        array(
            'title' => $translationPrefix . '.novelty',
            'name' => 'isNovelty',
            'formatter' => 'YesNo'
        )
    )
);
