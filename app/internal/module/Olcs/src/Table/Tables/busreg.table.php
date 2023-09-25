<?php

use Common\Service\Table\Formatter\BusRegNumberLink;
use Common\Service\Table\Formatter\Date;

return array(
    'variables' => array(
        'titleSingular' => 'Bus registration',
        'title' => 'Bus registrations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'govuk-button'),
                'edit' => array('requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => BusRegNumberLink::class,
            'sort' => 'routeNo',
        ),
        array(
            'title' => 'Var No.',
            'isNumeric' => true,
            'name' => 'variationNo',
            'sort' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
            'isNumeric' => true, //mostly numeric so using the style
            'name' => 'serviceNo',
            'sort' => 'serviceNo'
        ),
        array(
            'title' => '1st registered / cancelled',
            'formatter' => Date::class,
            'name' => 'date1stReg'
        ),
        array(
            'title' => 'Starting point',
            'name' => 'startPoint',
            'sort' => 'startPoint'
        ),
        array(
            'title' => 'Finishing point',
            'name' => 'finishPoint',
            'sort' => 'finishPoint'
        ),
        array(
            'title' => 'Status',
            'name' => 'busRegStatusDesc',
            'sort' => 'busRegStatusDesc',
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
