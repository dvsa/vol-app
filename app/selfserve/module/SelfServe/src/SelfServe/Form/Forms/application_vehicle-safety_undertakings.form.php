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
                    'label' => $translationPrefix . '-smallVehiclesUndertakings'
                ),
                'elements' => array(
                    // 15b[i]
                    'psvOperateSmallVhl' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.smallVehiclesIntention.yesNo'
                    ),
                    // 15b[ii]
                    'psvSmallVhlNotes' => array(
                        'label' => $translationPrefix . '.smallVehiclesIntentionDetails.title',
                        'type' => 'textarea',
                        'class' => 'long',
                        'filters' => '\Common\Form\Elements\InputFilters\VehicleUndertakingsOperateSmallVehicles',
                    ),
                    // Notes to go along with 15c/d
                    'psvSmallVhlScotland' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakingsScotland.title',
                        'class' => 'long',
                        'type' => 'textarea'
                    ),
                    'psvSmallVhlUndertakings' => array(
                        'label' => $translationPrefix . '.smallVehiclesUndertakings.title',
                        'class' => 'long',
                        'type' => 'textarea'
                    ),
                    // 15c/d
                    'psvSmallVhlConfirmation' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.smallVehiclesConfirmation',
                        'filters' =>
                            '\Common\Form\Elements\InputFilters\VehicleUndertakingsOperateSmallVehiclesAgreement'
                    )
                ),
            ),
            array(
                'name' => 'nineOrMore',
                // 15e
                'elements' => array(
                    'psvNoSmallVhlConfirmationLabel' => array(
                        'type' => 'html',
                        'label' => $translationPrefix . '.nineOrMore.label'
                    ),
                    'psvNoSmallVhlConfirmation' => array(
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
                    'psvNoLimousineConfirmationLabel' => array(
                        'type' => 'html',
                        'label' => $translationPrefix . '.limousinesApplication.agreement.label'
                    ),
                    'psvNoLimousineConfirmation' => array(
                        'type' => 'singlecheckbox',
                        'label' => $translationPrefix . '.limousinesApplication.agreement'
                    ),
                    // 15g
                    'psvOnlyLimousinesConfirmationLabel' => array(
                        'type' => 'html',
                        'label' => $translationPrefix . '.limousinesNine.agreement.label'
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
