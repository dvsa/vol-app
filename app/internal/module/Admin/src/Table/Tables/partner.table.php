<?php

use Common\Service\Table\Formatter\Address;

return array(
    'variables' => array(
        'titleSingular' => 'Partner organisation',
        'title' => 'Partner organisations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button', 'requireRows' => false),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Name',
            'name' => 'description'
        ),
        array(
            'title' => 'Address',
            'formatter' => Address::class,
            'name' => 'address',
            'addressFields' => 'FULL',
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
