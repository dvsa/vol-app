<?php

$translationPrefix = 'application_vehicle-safety_safety';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'licence',
                'options' => array(0),
                'elements' => array(
                    'safetyInsVehicles' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . '.licence.vehicleInspectionInterval',
                        'value_options' => 'inspection_interval_vehicle',
                        'required' => true
                    ),
                    'safetyInsTrailers' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . '.licence.trailerInspectionInterval',
                        'value_options' => 'inspection_interval_trailer',
                        'required' => true
                    ),
                    'safetyInsVaries' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . '.licence.moreFrequentInspections',
                        'hint' => $translationPrefix . '.licence.moreFrequentInspectionsHint',
                        'required' => true
                    ),
                    'tachographIns' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.licence.tachographAnalyser',
                        'value_options' => 'tachograph_analyser',
                        'required' => true
                    ),
                    'tachographInsName' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\VehicleSafetyTachographAnalyserContractor',
                        'label' => $translationPrefix . '.licence.tachographAnalyserContractor'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    )
                )
            ),
            array(
                'name' => 'table',
                'options' => array(0),
                'type' => 'table-required'
            ),
            array(
                'name' => 'application',
                'options' => array(0),
                'elements' => array(
                    'safetyConfirmation' => array(
                        'type' => 'yesnocheckbox',
                        'label' => $translationPrefix . '.application.safetyConfirmation'
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
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
