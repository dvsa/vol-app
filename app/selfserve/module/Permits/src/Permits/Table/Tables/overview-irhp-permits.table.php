<?php

use Common\Util\Escape;

return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            ),
        ),
    ),
    'attributes' => array(),
    'columns' => array(

        array(
            'title' => 'permits.irhp.valid.permits.table.permit',
            'name' => 'permitNumber',
            'formatter' => 'NullableNumber',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.country',
            'name' => 'country',
            'formatter' => function ($row, $column, $sm) {
                $translator = $sm->get('translator');
                return Escape::html($translator->translate($row['irhpPermitApplication']['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc']));
            },
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.application',
            'name' => 'irhpPermitApplication',
            'stack' => 'irhpPermitApplication->id',
            'formatter' => 'StackValue',
        ),

        array(
            'title' => 'permits.irhp.valid.permits.table.start-date',
            'name' => 'issueDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.expiry-date',
            'name' => 'expiryDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'status',
            'name' => 'status',
            'formatter' => 'RefDataStatus',
        ),
    )
);
