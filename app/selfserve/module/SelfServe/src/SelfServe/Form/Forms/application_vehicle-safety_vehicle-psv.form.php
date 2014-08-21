<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'hasEnteredReg' => array(
                        'label' => $translationPrefix . '.hasEnteredReg',
                        'type' => 'yesNoRadio'
                    )
                )
            ),
            array(
                'name' => 'small',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'medium',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'large',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
