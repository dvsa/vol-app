<?php

return array(
  'variables' => array(
    'title' => 'dashboard-table-permits-title',
    'empty_message' => 'dashboard-no-permit-text',
    'hide_column_headers' => false,
  ),
  'settings' => array(),
  'attributes' => array(),
  'columns' => array(
    array(
      'title' => 'dashboard-table-permits-ref',
      'name' => 'id',
      'formatter' => 'LicencePermitReference'
    ),
    array(
      'title' => 'dashboard-table-permits-num',
      'name' => 'permitsRequired',
      'formatter' => 'Translate'
    ),
    array(
     'title' => 'dashboard-table-permits-type',
     'name' => 'permitType',
     'formatter' => 'RefData'
    ),
    array(
      'title' => 'dashboard-table-permits-status',
      'name' => 'status',
      'formatter' => 'RefDataStatus'
    )
  )
);
