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
                    'safety_ins_vehicles' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . 'vehicleInspectionInterval',
                        'value_options' => 'inspection_interval_vehicle',
                        'required' => true
                    ),
                    'safety_ins_trailers' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . 'trailerInspectionInterval',
                        'value_options' => 'inspection_interval_trailer',
                        'required' => true
                    ),
                    'safety_ins_varies' => array(
                        'type' => 'yesNoRadio',
                        'label' => $translationPrefix . 'moreFrequentInspections',
                        'required' => true
                    ),
                    'tachograph_ins' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . 'tachographAnalyser',
                        'value_options' => 'tachograph_analyser',
                        'required' => true
                    ),
                    'tachograph_ins_name' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\VehicleSafetyTachographAnalyserContractor',
                        'label' => $translationPrefix . 'tachographAnalyserContractor'
                    ),
                    'safety_confirmation' => array(
                        // @todo See if we can make this a checkbox
                        'type' => 'multicheckbox',
                        'label' => 'Confirm',
                        'value_options' => array(
                            $translationPrefix . 'safetyConfirmation'
                        )
                    )
                )
            )
        ),
        'elements' => array(
            'version' => array(
                'type' => 'hidden'
            ),
            'submit' => array(
                'type' => 'submit',
                'label' => 'Continue',
                'class' => 'action--primary large'
            )
        )
    )
);
