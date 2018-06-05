<?php

return array(
  'variables' => array(
    'title' => '',
    'empty_message' => 'dashboard-no-permit-text',
    'hide_column_headers' => false,
  ),
  'settings' => array(),
  'attributes' => array(),
  'columns' => array(
    array(
      'title' => 'dashboard-table-permits-id',
      'name' => 'id',
      'formatter' => 'Translate'
    ),
    array(
      'title' => 'dashboard-table-permits-restrictions',
      'name' => 'restrictions',
      'formatter' => 'YesNo'
    ),
    array(
      'title' => 'dashboard-table-permits-status',
      'name' => 'status',
      'formatter' => 'Status'
    )
  )
);
