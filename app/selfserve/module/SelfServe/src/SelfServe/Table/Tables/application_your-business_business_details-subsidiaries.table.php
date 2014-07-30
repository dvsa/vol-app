<?php

return array(
    'variables' => array(
        'title' => 'application_your-business_business_details-subsidiaries-tableHeader',
        'within_form' => true,
        'empty_message' => 'application_your-business_business_details-subsidiaries-tableEmptyMessage'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
            'width' => 'checkbox',
            'type' => 'Selector'
        ),
        array(
            'title' => 'application_your-business_business_details-subsidiaries-columnName',
            'name' => 'name',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'edit'
                    ),
                    'Application/YourBusiness/BusinessDetails'
                ) . '">' . $row['name'] . '</a>';
            }
        ),
        array(
            'title' => 'application_your-business_business_details-subsidiaries-columnCompanyNo',
            'name' => 'companyNo',
        ),
    ),
    // Footer configuration
    'footer' => array(
    )
);
