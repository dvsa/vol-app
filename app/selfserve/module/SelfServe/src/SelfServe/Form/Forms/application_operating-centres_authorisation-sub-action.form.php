<?php

$translationPrefix = 'application_operating-centres_authorisation-sub-action.data';

return array(
    'application_operating-centres_authorisation-sub-action' => array(
        'name' => 'application_operating-centres_authorisation-sub-action',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'options' => array(
                    'label' => 'Address',
                ),
                // this will ensure the common address fieldset is pulled in
                // to avoid having to redeclare it here
                'type' => 'address',
            ),
            'authorised-vehicles' => array(
                'name' => 'authorised-vehicles',
                'options' => array(
                    'label' => 'Vehicles & trailers',
                ),
                'elements' => array(
                    'no-of-vehicles' => array(
                        'type' => 'vehiclesNumber',
                        'label' => 'Total no. of vehicles',
                    ),
                    'no-of-trailers' => array(
                        'type' => 'vehiclesNumber',
                        'label' => 'Total no. of trailers',
                    ),
                    'parking-spaces-confirmation' => array(
                        'type' => 'checkbox',
                        'label' =>
                        'I have enough parking spaces available for the ' .
                        'total number of vehicles and trailers that I want ' .
                        'to keep at this address',
                        'options' => array(
                            'must_be_checked' => true,
                            'not_checked_message' => 'You must confirm that you have enough parking spaces',
                        ),
                    ),
                    'permission-confirmation' => array(
                        'type' => 'checkbox',
                        'label' =>
                        'I am either the site owner or have permission from ' .
                        'the site owner to use the premises to park the number ' .
                        'of vehicles and trailers stated',
                        'options' => array(
                            'must_be_checked' => true,
                            'not_checked_message' => 'You must confirm that you have permission to use the premises to
                                park the number of vehicles & trailers stated',
                        ),
                    ),
                )
            ),
            array(
                'name' => 'form-actions',
                'class' => 'action-buttons',
                'options' => array(),
                'attributes' => array('class' => 'actions-container'),
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
                    )
                )
            )
        )
    )
);
