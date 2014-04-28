<?php

$translationPrefix = 'selfserve-app-vehicle-safety-safety-';

return array(
    'vehicle-safety' => array(
        'name' => 'vehicle-safety',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'data',
                'options' => array(0),
                'elements' => array(
                    'licence.safetyInsVehicles' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . 'vehicleInspectionInterval',
                        'value_options' => 'inspection_interval_vehicle',
                        'required' => true
                    ),
                    'licence.safetyInsTrailers' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . 'trailerInspectionInterval',
                        'value_options' => 'inspection_interval_trailer',
                        'required' => true
                    ),
                    'licence.safetyInsVaries' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . 'moreFrequentInspections',
                        'required' => true
                    ),
                    'licence.tachographIns' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . 'tachographAnalyser',
                        'value_options' => 'tachograph_analyser',
                        'required' => true
                    ),
                    'licence.tachographInsName' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\VehicleSafetyTachographAnalyserContractor',
                        'label' => $translationPrefix . 'tachographAnalyserContractor'
                    ),
                    'safetyConfirmation' => array(
                        // @todo See if we can make this a checkbox
                        'type' => 'multicheckbox',
                        'label' => 'Confirm',
                        'value_options' => array(
                            '1' => $translationPrefix . 'safetyConfirmation'
                        )
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'licence.version' => array(
                        'type' => 'hidden'
                    ),
                    'licence.id' => array(
                        'type' => 'hidden'
                    )
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
