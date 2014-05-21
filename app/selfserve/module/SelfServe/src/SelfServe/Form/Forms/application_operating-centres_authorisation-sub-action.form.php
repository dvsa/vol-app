<?php

$translationPrefix = 'application_operating-centres_authorisation-sub-action';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'type' => 'address',
                'options' => array(
                    'label' => 'Address',
                )
            ),
            array(
                'name' => 'data',
                'options' => array(
                    'label' => $translationPrefix . '.data',
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'application' => array(
                        'type' => 'hidden'
                    ),
                    'numberOfVehicles' => array(
                        'type' => 'vehiclesNumber',
                        'label' => $translationPrefix . '.data.numberOfVehicles',
                    ),
                    'numberOfTrailers' => array(
                        'type' => 'vehiclesNumber',
                        'label' => $translationPrefix . '.data.numberOfTrailers',
                    ),
                    'sufficientParking' => array(
                        'type' => 'checkbox',
                        'label' => $translationPrefix . '.data.sufficientParking',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    ),
                    'permission' => array(
                        'type' => 'checkbox',
                        'label' => $translationPrefix . '.data.permission',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    )
                )
            ),
            array(
                'type' => 'journey-crud-buttons'
            ),
            array(
                'name' => 'operatingCentre',
                'options' => array(),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    )
                )
            )
        )
    )
);
