<?php

return array(
    'conviction' => array(
        'name' => 'conviction',
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
                'version' => array(
                    'type' => 'hidden',
                ),
                'title' => array(
                    'type' => 'select',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formTitle',
                    'value_options' => [
                        'Mr'   => 'selfserve-app-subSection-previous-history-criminal-conviction-formTitleValueMr',
                        'Mrs'  => 'selfserve-app-subSection-previous-history-criminal-conviction-formTitleValueMrs',
                        'Miss' => 'selfserve-app-subSection-previous-history-criminal-conviction-formTitleValueMiss',
                        'Ms'   => 'selfserve-app-subSection-previous-history-criminal-conviction-formTitleValueMs'
                    ],
                    'required' => true
                ),
                'first_name' => array(
                    'type' => 'text',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formFirstName',
                    'class' => 'long'
                ),
                'last_name' => array(
                    'type' => 'text',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formLastName',
                    'class' => 'long'
                ),
                'doc' => [
                    'type' => 'dateSelect',
                    'name' => 'doc',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formDateOfConviction',
                    'options' => [
                        'create_empty_option' => false,
                        'render_delimiters' => 'd m y'
                    ],
                    'attributes' => [
                        'id' => 'dob'
                    ]
                ],
                'offence_details' => array(
                    'label' => 'Offence',
                    'type' => 'convictionTextarea',
                    'class' => 'long',
                    'options' => [
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetails',
                        'help-block' =>
                         'selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetaisHelpBlock'
                    ],
                ),
                'name_of_court' => array(
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formNameOfCourt',
                    'type' => 'text',
                    'class' => 'long'
                ),
                'penalty' => array(
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formPenalty',
                    'type' => 'text',
                    'class' => 'long'
                )
                )
            ),
            array(
                'name' => 'form-actions',
                'class' => 'action-buttons',
                'attributes' => array('class' => 'actions-container'),
                'options' => array(),
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formSave',
                        'class' => 'action--primary large'
                    ),
                    'addAnother' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' =>
                            'selfserve-app-subSection-previous-history-criminal-conviction-formSaveAndAddAnother',
                        'class' => 'action--primary large'
                    ),
                    'cancel' => array(
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formCancel',
                        'class' => 'action--secondary large'
                    )
                )
            )
        )
    )
);
