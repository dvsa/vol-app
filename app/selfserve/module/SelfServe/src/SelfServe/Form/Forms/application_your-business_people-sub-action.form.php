<?php

return array(
    'application_your-business_people-sub-action' => array(
        'name' => 'application_your-business_people-sub-action',
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
                    'label' => 'application_your-business_people-sub-action-formTitle',
                    'value_options' => [
                        'Mr'   => 'application_your-business_people-sub-action-formTitleValueMr',
                        'Mrs'  => 'application_your-business_people-sub-action-formTitleValueMrs',
                        'Miss' => 'application_your-business_people-sub-action-formTitleValueMiss',
                        'Ms'   => 'application_your-business_people-sub-action-formTitleValueMs'
                    ],
                    'required' => true
                ),
                'forename' => array(
                    'type' => 'text',
                    'label' => 'application_your-business_people-sub-action-formFirstName',
                    'class' => 'long',
                    'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                ),
                'familyName' => array(
                    'type' => 'text',
                    'label' => 'application_your-business_people-sub-action-formSurname',
                    'class' => 'long',
                    'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                ),
                'otherName' => array(
                    'type' => 'text',
                    'label' => 'application_your-business_people-sub-action-formOtherNames',
                    'class' => 'long'
                ),
                'position' => array(
                    'type' => 'text',
                    'label' => 'application_your-business_people-sub-action-formPosition',
                    'class' => 'long',
                    'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                ),
                'birthDate' => [
                    'type' => 'dateSelect',
                    'label' => 'application_your-business_people-sub-action-formDateOfBirth',
                    'options' => [
                        'create_empty_option' => false,
                        'render_delimiters' => 'd m y'
                    ],
                    'attributes' => [
                        'id' => 'dob'
                    ]
                ],
                )
            ),
            array(
                'type' => 'journey-crud-buttons',
            )
        )
    )
);
