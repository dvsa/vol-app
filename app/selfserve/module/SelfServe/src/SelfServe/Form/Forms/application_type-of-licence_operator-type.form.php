<?php

return array(
    'application_type-of-licence_operator-type' => array(
        'name' => 'application_type-of-licence_operator-type',
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
                    'operator-type' => array(
                        'name' => 'operator-type',
                        'label' => 'What type of operator are you?',
                        'type' => 'radio',
                        'attributes' => array(
                            'id' => 'operator-location',
                            'class' => ''
                        ),
                        'value_options' => 'operator_types'
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
