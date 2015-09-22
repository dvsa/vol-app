<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv-medium.table';

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
                'add' => array('class' => 'primary', 'id' => 'addMedium'),
                'delete' => array('class' => 'secondary', 'requireRows' => true, 'id' => 'deleteMedium'),
                'transfer' => array(
                    'label' => 'Transfer',
                    'class' => 'secondary js-require--multiple',
                    'requireRows' => true,
                    'id' => 'transferMedium'
                )
            )
        ),
        'row-disabled-callback' => function ($row) {
            return $row['removalDate'] !== null;
        }
    ),
    'attributes' => array(
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
            'title' => $translationPrefix . '.specified',
            'formatter' => 'Date',
            'name' => 'specifiedDate'
        ),
        array(
            'title' => $translationPrefix . '.removed',
            'formatter' => 'Date',
            'name' => 'removalDate'
        ),
        array(
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true
        )
    )
);
