<?php

return array(
    'application_type-of-licence_licence-type' => array(
        'name' => 'application_type-of-licence_licence-type',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => 'application_type-of-licence_licence-type.data'
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
