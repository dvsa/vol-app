<?php
return array(
    'variables' => array(),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'attributes' => array(),
    'columns' => array(
        array(
                'title' => 'permits.ecmt.page.valid.tableheader.ref',
                'name' => 'permitNumber',
                'formatter' => function ($row) {
                    return '<b>' . $row['permitNumber'] . '</b>';
                },
            ),
        array(
            'title' => 'permits.ecmt.page.valid.tableheader.countries',
            'name' => 'countries',
            'formatter' => 'translate'
        )
    )
);
