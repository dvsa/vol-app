<?php

$translationPrefix = 'application_vehicle-safety_undertakings';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'application',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'status' => array(
                        'type' => 'hidden'
                    ),
                ),
            ),
            array(
                'name' => 'smallVehiclesIntention',
                'options' => array(
                    'label' => $translationPrefix . '-smallVehiclesIntention'
                ),
                'elements' => array(
                    // 15b[i]
                    'psvOperateSmallVehicles' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.smallVehiclesIntention.yesNo'
                    ),
                    // 15b[ii]
                    'psvSmallVehicleNotes' => array(
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
                    'psvSmallVehicleScotland' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakingsScotland.title',
                        'class' => 'long',
                        'type' => 'textarea',
                    ),
                    'psvSmallVehicleUndertakings' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakings.title',
                        'class' => 'long',
                        'type' => 'textarea',
                    ),
                    // 15c/d
                    'psvSmallVehicleConfirmation' => array(
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
                // 15e
                'elements' => array(
                    'psvNoSmallVehiclesConfirmation' => array(
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
                    // 15f[i]
                    'psvLimousines' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.limousinesApplication.yesNo'
                    ),
                    // 15f[ii]
                    'psvNoLimousineConfirmation' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.limousinesApplication.agreement'
                    ),
                    // 15g
                    'psvOnlyLimousinesConfirmation' => array(
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
