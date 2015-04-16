<?php

return array(
    'variables' => array(
        'title' => 'Transport manager forms'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Application No.',
            'formatter' => 'DashboardTmApplicationId'
        ),
        array(
            'title' => 'Licence No.',
            'name' => 'licNo',
        ),
        array(
            'title' => '',
            'formatter' => 'DashboardTmActionLink'
        )
    )
);
