<?php

$translationPrefix = 'application_vehicle-safety_undertakings';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'smallVehiclesIntention',
                'options' => array(
                    'label' => $translationPrefix . '-smallVehiclesIntention'
                ),
                'elements' => array(
                    'optSmallVehiclesIntention' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.smallVehiclesIntention.yesNo'
                    ),
                    'txtSmallVehiclesIntentionDetails' => array(
                        'label' => $translationPrefix . '.smallVehiclesIntentionDetails.title',
                        'type' => 'textarea',
                        'class' => 'long',
                    )
                ),
            ),
            array(
                'name' => 'smallVehiclesUndertakings',
                'options' => array(
                    'label' => $translationPrefix . '-smallVehiclesUndertakings'
                ),
                'elements' => array(
                    'smallVehiclesScotland' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakingsScotland.title',
                        'type' => 'textarea',
                        'class' => 'long',
                    ),
                    'smallVehiclesUndertakings' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakings.title',
                        'type' => 'textarea',
                        'class' => 'long',
                    ),
                    'optSmallVehiclesConfirmation' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.smallVehiclesConfirmation'
                    )
                ),
            ),
            array(
                'name' => 'nineOrMore',
                'options' => array(
                    'label' => $translationPrefix . '-nineOrMore',
                    'hint' => $translationPrefix . '-nineOrMore.hint'
                ),
                'elements' => array(
                    'textNineOrMorePassengers' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.nineOrMore.details'
                    )
                ),
            ),
            array(
                'name' => 'limousinesNoveltyVehicles',
                'options' => array(
                    'label' => $translationPrefix . '-limousines'
                ),
                'elements' => array(
                    'optLimousinesYesNo' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.limousinesApplication.yesNo'
                    ),
                    'optAgreement' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.limousinesApplication.agreement'
                    ),
                    'optLimousinesNine' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.limousinesNine.agreement'
                    )
                ),
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
