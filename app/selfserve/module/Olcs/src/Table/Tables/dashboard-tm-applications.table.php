<?php

return array(
    'variables' => array(
        'title' => '',
        'empty_message' => 'dashboard.tm-applications.table.EmptyMessage'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'dashboard.tm-applications.table.column.operator.title',
            'name' => 'operatorName'
        ),
        array(
            'title' => 'dashboard.tm-applications.table.column.app-no.title',
            'formatter' => 'DashboardTmApplicationId'
        ),
        array(
            'title' => 'dashboard.tm-applications.table.column.lic-no.title',
            'name' => 'licNo',
        ),
        array(
            'title' => '',
            'formatter' => 'DashboardTmActionLink'
        )
    )
);
