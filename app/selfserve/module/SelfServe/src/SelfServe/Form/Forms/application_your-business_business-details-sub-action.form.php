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
                    'type' => 'text',
                    'label' => 'application_your-business_business-details-formName',
                    'class' => 'long',
                    'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                ),
                'companyNo' => array(
                    'type' => 'text',
                    'label' => 'application_your-business_business-details-formCompanyNo',
                    'class' => 'long',
                    'filters' => '\Common\Form\Elements\InputFilters\CompanyNumber'
                ),
                )
            ),
            array(
                'type' => 'journey-crud-buttons',
            )
        )
    )
);
