<?php

return array(
    'application_your-business_addresses' => array(
        'name' => 'application_your-business_addresses',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'correspondence',
                'elements' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'version',
                    ],
                ]
            ),
            array(
                'name' => 'correspondence_address',
                'type' => 'address',
                'options' => array(
                    'label' => 'application_your-business_business-type.correspondence.label'
                ),
            ),
            array(
                'name' => 'contact',
                'options' => array(
                    'label' => 'application_your-business_business-type.contact-details.label',
                    'hint' => 'application_your-business_business-type.contact-details.hint',
                ),
                'elements' => [
                    [
                        'type' => 'hiddenPhoneValidation',
                        'name' => 'phone-validator',
                    ],
                    [
                        'type' => 'phone',
                        'name' => 'phone_business',
                        'label' => 'application_your-business_business-type.contact-details.business-phone',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_business_id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_business_version',
                    ],
                    [
                        'type' => 'phone',
                        'name' => 'phone_home',
                        'label' => 'application_your-business_business-type.contact-details.home-phone',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_home_id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_home_version',
                    ],
                    [
                        'type' => 'phone',
                        'name' => 'phone_mobile',
                        'label' => 'application_your-business_business-type.contact-details.mobile-phone',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_mobile_id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_mobile_version',
                    ],
                    [
                        'type' => 'phone',
                        'name' => 'phone_fax',
                        'label' => 'application_your-business_business-type.contact-details.fax-phone',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_fax_id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'phone_fax_version',
                    ],
                    [
                        'type' => 'email',
                        'name' => 'email',
                        'label' => 'application_your-business_business-type.contact-details.email',
                    ]
                ],
            ),
            array(
                'name' => 'establishment',
                'elements' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'version',
                    ],
                ]
            ),
            array(
                'name' => 'establishment_address',
                'type' => 'address',
                'options' => array(
                    'label' => 'application_your-business_business-type.establishment.label'
                ),
            ),
            array(
                'name' => 'registered_office',
                'elements' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'version',
                    ],
                ]
            ),
            array(
                'name' => 'registered_office_address',
                'type' => 'address',
                'options' => array(
                    'label' => 'application_your-business_business-type.registered-office.label'
                ),
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
