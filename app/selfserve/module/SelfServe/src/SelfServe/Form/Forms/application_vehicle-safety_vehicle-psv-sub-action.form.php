<?php

$translationPrefix = 'application_vehicle-safety_vehicle-psv-sub-action';

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
                    'psvType' => array(
                        'type' => 'hidden'
                    ),
                    'vrm' => array(
                        'type' => 'vehicleVrm',
                        'label' => $translationPrefix. '.data.vrm'
                    ),
                    'makeModel' => array(
                        'type' => 'text',
                        'label' => $translationPrefix. '.data.makeModel'
                    ),
                    'isNovelty' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix. '.data.isNovelty'
                    )
                )
            ),
            array(
                'type' => 'journey-crud-buttons'
            )
        )
    )
);
