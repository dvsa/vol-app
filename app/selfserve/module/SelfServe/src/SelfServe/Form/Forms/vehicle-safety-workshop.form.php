<?php

$translationPrefix = 'selfserve-app-vehicle-safety-workshop-';

return array(
    'vehicle-safety-workshop' => array(
        'name' => 'vehicle-safety-workshop',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'data',
                'options' => array(0),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'applicationId' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'isExternal' => array(
                        'type' => 'radio',
                        'label' => 'Who will carry out the safety inspections?',
                        'value_options' => array(
                            'N' => 'Yourself or another employee of the business holding the operator\s licence',
                            'Y' => 'An external contractor'
                        )
                    ),
                    'organisation' => array(
                        'type' => 'text',
                        'label' => 'Contractor\'s name or person\'s full name if performed internally',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                    )
                )
            ),
            array(
                'name' => 'address',
                'options' => array(
                    'label' => 'Address'
                ),
                'type' => 'address'
            ),
            array(
                'name' => 'form-actions',
                'class' => 'action-buttons',
                'options' => array(0),
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Continue',
                        'class' => 'action--primary large'
                    ),
                    'cancel' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    )
                )
            )
        )
    )
);
