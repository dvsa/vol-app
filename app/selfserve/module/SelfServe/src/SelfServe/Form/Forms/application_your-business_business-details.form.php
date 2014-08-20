<?php

$translationPrefix = 'application_your-business';
$detailsPrefix = $translationPrefix .  '_business-details.data.';

return array(
    'application_your-business_business-details' => array(
        'name' => 'application_your-business_business-details',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'type' => array(
                        'label' => $translationPrefix . '_business-type.data.type',
                        'type' => 'selectDisabled',
                        'value_options' => 'business_types',
                        'class' => 'inline'
                    ),
                    'edit_business_type' => array(
                        'type' => 'submit',
                        'label' => 'Edit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionLink',
                        'route' => 'Application/YourBusiness/BusinessType'
                    ),
                    'companyNumber' => array(
                        'type' => 'companyNumber',
                        'label' => $detailsPrefix . 'company_number',
                    ),
                    'name' => array(
                        'type' => 'companyName',
                        'label' => $detailsPrefix . 'company_name',
                    ),
                    'tradingNames' => array(
                        'label' => $detailsPrefix . 'trading_names_optional',
                        'type' => 'tradingNames'
                    ),
                )
            ),
            array(
                'name' => 'table',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
