<?php

return array(
  'variables' => array(
    'title' => 'dashboard-table-permit-application-title',
    'empty_message' => 'dashboard-no-permit-text',
    'hide_column_headers' => false,
  ),
  'settings' => array(),
  'attributes' => array(),
  'columns' => array(
    array(
      'title' => 'dashboard-table-permit-application-ref',
      'name' => 'id',
      'formatter' => 'LicencePermitReference'
    ),
    array(
      'title' => 'dashboard-table-permit-application-num',
      'name' => 'permitsRequired',
      'formatter' => 'Translate'
    ),
    array(
      'title' => 'dashboard-table-permit-application-type',
      'name' => 'permitType',
      'formatter' => 'RefData'
    ),
    array(
      'title' => 'dashboard-table-permit-application-status',
      'name' => 'status',
      'formatter' => 'RefDataStatus'
    )
  )
);
