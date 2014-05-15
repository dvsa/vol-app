<?php

$translationPrefix = 'application_operating-centres_authorisation-sub-action.data';

return array(
    'application_operating-centres_authorisation-sub-action' => array(
        'name' => 'application_operating-centres_authorisation-sub-action',
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
                    'label' => $translationPrefix,
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
                        'label' => $translationPrefix . '.numberOfVehicles',
                    ),
                    'numberOfTrailers' => array(
                        'type' => 'vehiclesNumber',
                        'label' => $translationPrefix . '.numberOfTrailers',
                    ),
                    'sufficientParking' => array(
                        'type' => 'checkbox',
                        'label' => $translationPrefix . '.sufficientParking',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    ),
                    'permission' => array(
                        'type' => 'checkbox',
                        'label' => $translationPrefix . '.permission',
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
