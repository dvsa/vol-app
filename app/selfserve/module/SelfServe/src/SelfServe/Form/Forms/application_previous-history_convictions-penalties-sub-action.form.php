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
                    'title' => array(
                        'type' => 'select',
                        'label' => $translationPrefix . '-formTitle',
                        // @todo Not sure these should be hard coded in here
                        'value_options' => array(
                            'Mr' => $translationPrefix . '-formTitleValueMr',
                            'Mrs' => $translationPrefix . '-formTitleValueMrs',
                            'Miss' => $translationPrefix . '-formTitleValueMiss',
                            'Ms' => $translationPrefix . '-formTitleValueMs'
                        ),
                        'required' => true
                    ),
                    'forename' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => $translationPrefix . '-formFirstName',
                        'class' => 'long'
                    ),
                    'familyName' => array(
                        'type' => 'text',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired',
                        'label' => $translationPrefix . '-formLastName',
                        'class' => 'long'
                    ),
                    'convictionDate' => array(
                        'type' => 'dateSelectWithEmpty',
                        'label' => $translationPrefix . '-formDateOfConviction'
                    ),
                    'categoryText' => array(
                        'label' => $translationPrefix . '-formOffence',
                        'type' => 'text',
                        'class' => 'long'
                    ),
                    'notes' => array(
                        'label' => $translationPrefix . '-formOffenceDetails',
                        'type' => 'convictionTextarea',
                        'class' => 'long',
                        'hint' => $translationPrefix . '-formOffenceDetaisHelpBlock'
                    ),
                    'courtFpn' => array(
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
