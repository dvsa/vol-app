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
            'isNumeric' => true,
            'name' => 'applicationId'
        ),
        array(
            'title' => 'dashboard-table-applications-status',
            'formatter' => 'DashboardTmApplicationStatus'
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
