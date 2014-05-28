<?php

return array(
    'application_your-business_people' => array(
        'name' => 'application_your-business_people',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'table',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'guidance',
                'options' => array(0),
                'elements' => array(
                    'guidance' => array(
                        'name' => 'guidance',
                        'type' => 'html',
                        'attributes' => array(
                            'value' => 'selfserve-app-subSection-your-business-people-guidance'
                        )
                    ),
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
