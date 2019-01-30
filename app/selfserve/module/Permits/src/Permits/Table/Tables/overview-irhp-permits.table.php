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
            'title' => 'permits.irhp.valid.permits.table.col1',
            'name' => 'permitNumber',
            'formatter' => 'NullableNumber',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.col2',
            'name' => 'country',
            'formatter' => function ($row, $column, $sm) {
                $translator = $sm->get('translator');
                return Escape::html($translator->translate($row['irhpPermitApplication']['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc']));
            },
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.col3',
            'name' => 'irhpPermitApplication',
            'formatter' => function ($row) {
                return Escape::html($row['irhpPermitApplication']['id']);
            }
        ),

        array(
            'title' => 'permits.irhp.valid.permits.table.col4',
            'name' => 'issueDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'permits.irhp.valid.permits.table.col5',
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
