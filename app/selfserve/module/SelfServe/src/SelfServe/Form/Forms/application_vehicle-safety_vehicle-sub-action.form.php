<?php

$translationPrefix = 'application_vehicle-safety_vehicle-sub-action';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post'
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'vrm' => array(
                        'label' => $translationPrefix. '.data.vrm',
                        'type' => 'vehicleVrm'
                    ),
                    'platedWeight' => array(
                        'label' => $translationPrefix. '.data.weight',
                        'type' => 'vehicleGPW',
                        'filters' => '\Common\Form\Elements\InputFilters\VehicleWeight',
                    )
                    //NOT PART OF THE STORY (2057)
                    /* 'body_type' => array(
                      'type' => 'radio',
                      'value_options' => 'vehicle_body_types',
                      'options' => array(
                      'label' => 'Body type:',
                      ),
                      ), */
                )
            ),
            array(
                'type' => 'journey-crud-buttons'
            )
        )
    )
);
