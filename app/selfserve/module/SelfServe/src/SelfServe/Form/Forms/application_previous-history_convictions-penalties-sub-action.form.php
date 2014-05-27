<?php

return array(
    'application_previous-history_convictions-penalties-sub-action' => array(
        'name' => 'application_previous-history_convictions-penalties-sub-action',
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
                'personTitle' => array(
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
                'personFirstname' => array(
                    'type' => 'text',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formFirstName',
                    'class' => 'long'
                ),
                'personLastname' => array(
                    'type' => 'text',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formLastName',
                    'class' => 'long'
                ),
                'dateOfConviction' => [
                    'type' => 'dateSelect',
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formDateOfConviction',
                    'options' => [
                        'create_empty_option' => false,
                        'render_delimiters' => 'd m y'
                    ],
                    'attributes' => [
                        'id' => 'dob'
                    ]
                ],
                'convictionNotes' => array(
                    'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetails',
                    'type' => 'convictionTextarea',
                    'class' => 'long',
                    'hint' => 'selfserve-app-subSection-previous-history-criminal-conviction-formOffenceDetaisHelpBlock'
                ),
                'courtFpm' => array(
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
                'type' => 'journey-crud-buttons',
            )
        )
    )
);
