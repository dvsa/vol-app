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
                    'label' => 'Operator details'
                ),
                'elements' => array(
                    'operator-location' => array(
                        'name' => 'operator_location',
                        'label' => 'Where do you operate from?',
                        'type' => 'radio',
                        'attributes' => array(
                            'id' => 'operator-location',
                            'class' => ''
                        ),
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
