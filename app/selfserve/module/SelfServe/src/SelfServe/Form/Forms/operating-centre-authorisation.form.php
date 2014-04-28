<?php

$translationPrefix = 'selfserve-app-operating-centre-auth';

return array(
    'operating-centre-authorisation' => array(
        'name' => 'operating-centre-authorisation',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'totAuthVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '-totAuthVehicles',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreVehicleAuthorisations'
                    ),
                    'totAuthTrailers' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '-totAuthTrailers',
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
                    ),
                )
            )
        ),
        'elements' => array(
            'submit' => array(
                'type' => 'submit',
                'label' => 'Continue',
                'class' => 'action--primary large'
            )
        )
    )
);
