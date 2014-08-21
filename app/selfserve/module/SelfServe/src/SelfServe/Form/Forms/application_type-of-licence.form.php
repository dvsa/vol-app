<?php

return array(
    'application_type-of-licence' => array(
        'name' => 'application_type-of-licence',
        'attributes' => array(
            'method' => 'post',
            'class' => 'js-submit',
        ),
        'fieldsets' => array(
            array(
                'name' => 'operator-location',
                'options' => array(
                    'label' => 'application_type-of-licence_operator-location.data',
                ),
                'attributes' => array(
                    'class' => 'hidden',
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
                        'type' => 'yesNoRadio',
                        'value_options' => 'operator_locations'
                    )
                )
            ),
            array(
                'name' => 'operator-type',
                'options' => array(
                    'label' => 'application_type-of-licence_operator-type.data'
                ),
                'attributes' => array(
                    'class' => 'hidden',
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
                'name' => 'licence-type',
                'options' => array(
                    'label' => 'application_type-of-licence_licence-type.data'
                ),
                'attributes' => array(
                    'class' => 'hidden',
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'licenceType' => array(
                        'type' => 'radio',
                        'label' => 'application_type-of-licence_licence-type.data.licenceType',
                        'value_options' => 'licence_types',
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
