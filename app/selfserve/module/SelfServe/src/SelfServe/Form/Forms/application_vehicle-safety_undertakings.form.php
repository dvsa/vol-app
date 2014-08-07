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
                        'type' => 'textarea',
                        'class' => 'long',
                    ),
                    'psvSmallVehicleUndertakings' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakings.title',
                        'type' => 'textarea',
                        'class' => 'long',
                    ),
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
                'elements' => array(
                    'psvNoSmallVehicleConfirmation' => array(
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
                    'psvLimousines' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.limousinesApplication.yesNo'
                    ),
                    'psvNoLimousinesConfirmation' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.limousinesApplication.agreement'
                    ),
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
