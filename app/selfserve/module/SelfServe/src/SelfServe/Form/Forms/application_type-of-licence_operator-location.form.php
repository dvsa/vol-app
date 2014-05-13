<?php

return array(
    'application_type-of-licence_operator-location' => array(
        'name' => 'application_type-of-licence_operator-location',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => 'application_type-of-licence_operator-location.data'
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'niFlag' => array(
                        'label' => 'application_type-of-licence_operator-location.data.niFlag',
                        'type' => 'radio',
                        'value_options' => 'operator_locations'
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
