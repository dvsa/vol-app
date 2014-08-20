<?php

return array(
    'application_your-business_business-type' => array(
        'name' => 'application_your-business_business-type',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => 'application_your-business_business-type.data'
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'type' => array(
                        'label' => 'application_your-business_business-type.data.type',
                        'type' => 'select',
                        'value_options' => 'business_types',
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
