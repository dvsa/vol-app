<?php

return array(
    'variables' => array(
        'title' => 'selfserve-app-subSection-your-business-people-tableHeaderPartners',
        'within_form' => true,
        'empty_message' => 'selfserve-app-subSection-your-business-people-tableEmptyMessage',
        'required_label' => 'person',
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
            'title' => 'selfserve-app-subSection-your-business-people-columnName',
            'name' => 'name',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array(
                        'id' => $row['id'],
                        'action' => 'edit'
                    ),
                    'Application/YourBusiness/People'
                ) . '">' . $row['title'] . ' ' . $row['forename'] . ' ' . $row['familyName'] . '</a>';
            },
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnHasOtherNames',
            'name' => 'otherName',
            'formatter' => function ($row) {
                return ($row['otherName'] ? 'Yes' : 'No');
            }
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnDate',
            'name' => 'birthDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'selfserve-app-subSection-your-business-people-columnPosition',
            'name' => 'position',
        )
    ),
    // Footer configuration
    'footer' => array(
    )
);
