<?php

return array(
    'variables' => array(
        'title' => '',
        'empty_message' => 'dashboard-tm-applications.table.EmptyMessage'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'Application No.',
            'formatter' => 'DashboardTmApplicationId'
        ),
        array(
            'title' => 'Licence number',
            'name' => 'licNo',
        ),
        array(
            'title' => '',
            'formatter' => 'DashboardTmActionLink'
        )
    )
);
