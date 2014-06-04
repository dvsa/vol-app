<?php

return array(
    'application_your-business_business-details-sub-action' => array(
        'name' => 'application_your-business_business-details-sub-action',
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
                'name' => array(
                    'type' => 'companyName',
                    'label' => 'application_your-business_business-details-formName',
                    'class' => 'long',
                ),
                'companyNo' => array(
                    'type' => 'companyNumber',
                    'label' => 'application_your-business_business-details-formCompanyNo',
                    'class' => 'long',
                ),
                )
            ),
            array(
                'type' => 'journey-crud-buttons',
            )
        )
    )
);
