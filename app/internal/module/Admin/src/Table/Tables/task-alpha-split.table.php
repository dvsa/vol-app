<?php

return array(
    'variables' => array(
        'titleSingular' => 'Alpha split',
        'title' => 'Alpha splits',
        'within_form' => true,
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'addAlphasplit' => array('class' => 'govuk-button', 'label' => 'add'),
                'editAlphasplit' => array(
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'edit',
                    'requireRows' => true
                ),
                'deleteAlphasplit' => array(
                    'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                    'label' => 'delete',
                    'requireRows' => true
                )
            )
        ),
        // This has to exist so that the title gets prepended with the document count
        'paginate' => array(
        )
    ),

    'columns' => array(
        array(
            'title' => 'User',
            'name' => 'user->contactDetails->person',
            'formatter' => 'Name',
        ),
        array(
            'title' => 'Assign operator tasks starting with these letters',
            'formatter' => function ($data) {
                return $data['letters'];
            }
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
