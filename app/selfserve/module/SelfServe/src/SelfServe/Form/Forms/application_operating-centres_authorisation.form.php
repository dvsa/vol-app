<?php

$translationPrefix = 'application_operating-centres_authorisation.data';
$translationPrefixTrafficArea = 'application_operating-centres_authorisation.dataTrafficArea';

return array(
    'application_operating-centres_authorisation' => array(
        'name' => 'application_operating-centres_authorisation',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'table',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataTrafficArea',
                'elements' => array(
                    'trafficArea' => array(
                        'type' => 'select',
                        'value_options' => array(),
                        'required' => true,
                        'label' => $translationPrefixTrafficArea . '.label.new',
                        'hint' => $translationPrefixTrafficArea . '.hint.new',
                    ),
                    'trafficAreaInfoLabelExists' => array(
                        'type' => 'htmlTranslated',
                        'attributes' => array(
                            'value' => $translationPrefixTrafficArea . '.label.exists'
                        )
                    ),
                    'trafficAreaInfoNameExists' => array(
                        'type' => 'html',
                        'attributes' => array(
                            'value' => '<b>%NAME%</b>'
                        ),
                    ),
                    'trafficAreaInfoHintExists' => array(
                        'type' => 'htmlTranslated',
                        'attributes' => array(
                            'value' => $translationPrefixTrafficArea . '.labelasahint.exists'
                        )
                    ),
                    'hiddenId' => array(
                        'type' => 'hidden'
                    )
                )
            ),
            array(
                'name' => 'data',
                'options' => array(
                    'label' => $translationPrefix,
                    'hint' => $translationPrefix . '.hint'
                ),
                'elements' => array(
                    'licenceType' => array(
                        'type' => 'hidden'
                    ),
                    'totAuthSmallVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthSmallVehicles',
                        'class' => 'short',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreVehicleAuthorisations'
                    ),
                    'totAuthMediumVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthMediumVehicles',
                        'class' => 'short',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreVehicleAuthorisations'
                    ),
                    'totAuthLargeVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthLargeVehicles',
                        'class' => 'short',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreVehicleAuthorisations'
                    ),
                    'totCommunityLicences' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totCommunityLicences',
                        'class' => 'short',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreCommunityLicences'
                    ),
                    'totAuthVehicles' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthVehicles',
                        'class' => 'short',
                        'filters' => '\Common\Form\Elements\InputFilters\OperatingCentreTotalVehicleAuthorisations'
                    ),
                    'totAuthTrailers' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.totAuthTrailers',
                        'class' => 'short',
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
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
