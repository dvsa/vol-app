<?php

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'search-result-label-lic-no',
            'name' => 'licNo'
        ),
        array(
            'title' => 'search-result-label-licence-status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
        array(
            'title' => 'search-result-label-continuation-date',
            'formatter' => 'Date',
            'name' => 'expiryDate'
        )
    )
);
