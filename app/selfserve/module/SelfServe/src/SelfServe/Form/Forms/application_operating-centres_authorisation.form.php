<?php

$translationPrefix = 'application_operating-centres_authorisation.data';

return array(
    'application_operating-centres_authorisation' => array(
        'name' => 'application_operating-centres_authorisation',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => $translationPrefix,
                    'hint' => $translationPrefix . '.hint'
                ),
                'elements' => array(
                    'totAuthVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthVehicles',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreVehicleAuthorisations'
                    ),
                    'totAuthTrailers' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthTrailers',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreTrailerAuthorisations'
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'noOfOperatingCentres' => array(
                        'type' => 'hidden'
                    ),
                    'minVehicleAuth' => array(
                        'type' => 'hidden'
                    ),
                    'maxVehicleAuth' => array(
                        'type' => 'hidden'
                    ),
                    'minTrailerAuth' => array(
                        'type' => 'hidden'
                    ),
                    'maxTrailerAuth' => array(
                        'type' => 'hidden'
                    )
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
