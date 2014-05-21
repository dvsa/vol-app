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
                    'label' => 'application_type-of-licence_operator-type.data'
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'goodsOrPsv' => array(
                        'label' => 'application_type-of-licence_operator-type.data.goodsOrPsv',
                        'type' => 'radio',
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
