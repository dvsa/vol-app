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
                    'noOfVehiclesPossessed' => array(
                        'type' => 'vehiclesNumber',
                        'label' => $translationPrefix . '.data.noOfVehiclesPossessed',
                        'filters' => '\Common\Form\Elements\InputFilters\NumberOfVehicles',
                    ),
                    'noOfTrailersPossessed' => array(
                        'type' => 'vehiclesNumber',
                        'label' => $translationPrefix . '.data.noOfTrailersPossessed',
                        'filters' => '\Common\Form\Elements\InputFilters\NumberOfVehicles',
                    ),
                    'sufficientParking' => array(
                        'type' => 'yesnocheckbox',
                        'label' => $translationPrefix . '.data.sufficientParking',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    ),
                    'permission' => array(
                        'type' => 'yesnocheckbox',
                        'label' => $translationPrefix . '.data.permission',
                        'options' => array(
                            'must_be_checked' => true
                        )
                    ),
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
                    'adPlacedDate' => array(
                        'type' => 'dateSelectWithEmpty',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreDateAdPlaced',
                        'label' => $translationPrefix . '.advertisements.adPlacedDate'
                    ),
                    'file' => array(
                        'type' => 'multipleFileUpload',
                        'label' => $translationPrefix . '.advertisements.file',
                        'hint' => $translationPrefix . '.advertisements.file.hint'
                    )
                )
            ),
            array(
                'name' => 'trafficArea',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
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
