<?php

use Common\Service\Table\Formatter\SystemParameterLink;

return array(
    'variables' => array(
        'title' => 'parameters',
        'titleSingular' => 'parameter'
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
            ),
        )
    ),
    'columns' => array(
        array(
            'title' => 'Key',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
            'formatter' => SystemParameterLink::class
        ),
        array(
            'title' => 'Value',
            'name' => 'paramValue',
            'sort' => 'paramValue',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
