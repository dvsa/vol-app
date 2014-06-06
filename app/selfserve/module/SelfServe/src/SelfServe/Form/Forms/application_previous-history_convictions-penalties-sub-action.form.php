<?php

$translationPrefix = 'selfserve-app-subSection-previous-history-criminal-conviction';

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
                        'label' => $translationPrefix . '-formTitle',
                        'value_options' => [
                            'Mr' => $translationPrefix . '-formTitleValueMr',
                            'Mrs' => $translationPrefix . '-formTitleValueMrs',
                            'Miss' => $translationPrefix . '-formTitleValueMiss',
                            'Ms' => $translationPrefix . '-formTitleValueMs'
                        ],
                        'required' => true
                    ),
                    'personFirstname' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => $translationPrefix . '-formFirstName',
                        'class' => 'long'
                    ),
                    'personLastname' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => $translationPrefix . '-formLastName',
                        'class' => 'long'
                    ),
                    'dateOfConviction' => [
                        'type' => 'dateSelectWithEmpty',
                        'label' => $translationPrefix . '-formDateOfConviction'
                    ],
                    'convictionNotes' => array(
                        'label' => $translationPrefix . '-formOffenceDetails',
                        'type' => 'convictionTextarea',
                        'class' => 'long',
                        'hint' => $translationPrefix . '-formOffenceDetaisHelpBlock'
                    ),
                    'courtFpm' => array(
                        'label' => $translationPrefix . '-formNameOfCourt',
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'class' => 'long'
                    ),
                    'penalty' => array(
                        'label' => $translationPrefix . '-formPenalty',
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
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
