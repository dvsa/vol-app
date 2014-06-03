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
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.data.sufficientParking',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    ),
                    'permission' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.data.permission',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    )
                )
            ),
            array(
                'name' => 'advertisements',
                'options' => array(
                    'label' => $translationPrefix . '.advertisements'
                ),
                'elements' => array(
                    'adPlaced' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.advertisements.adPlaced'
                    ),
                    'adPlacedIn' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreAdPlacedIn',
                        'label' => $translationPrefix . '.advertisements.adPlacedIn'
                    ),
                    'dateAdPlaced' => array(
                        'type' => 'dateSelectWithEmpty',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreDateAdPlaced',
                        'label' => $translationPrefix . '.advertisements.dateAdPlaced'
                    ),
                    'file' => array(
                        'type' => 'multipleFileUpload',
                        'label' => $translationPrefix . '.advertisements.file',
                        'hint' => $translationPrefix . '.advertisements.file.hint'
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
