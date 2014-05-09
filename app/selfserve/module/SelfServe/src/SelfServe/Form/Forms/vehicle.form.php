<?php

return array(
    'vehicle' => array(
        'name' => 'vehicle',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'id' => array(
                    'type' => 'hidden',
                ),
                'vrm' => array(
                    'label' => 'Vehicle Registration Mark (VRM)',
                    'type' => 'vehicleVrm',
                ),
                'plated_weight' => array(
                    'label' => 'Gross Plated Weight (Kg)',
                    'type' => 'vehicleGPW',
                ),
                'version' => array(
                    'type' => 'hidden',
                )
                //NOT PART OF THE STORY (2057)
                /* 'body_type' => array(
                  'type' => 'radio',
                  'value_options' => 'vehicle_body_types',
                  'options' => array(
                  'label' => 'Body type:',
                  ),
                  ), */
                )
            ),
            array(
                'name' => 'form-actions',
                'class' => 'action-buttons',
                'options' => array(0),
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save',
                        'class' => 'action--primary large'
                    ),
                    'addAnother' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save & add another',
                        'class' => 'action--primary large'
                    ),
                    'cancel' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    ),
                    'home' => array(
                        'type' => 'submit',
                        'label' => 'Back to home',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionLink'
                    )
                )
            )
        )
    )
);
