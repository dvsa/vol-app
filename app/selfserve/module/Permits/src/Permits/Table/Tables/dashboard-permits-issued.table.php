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
      'title' => 'dashboard-table-permit-application-ref',
      'name' => 'id',
      'formatter' => 'LicencePermitReference'
    ),
    array(
      'title' => 'dashboard-table-permit-application-num',
      'name' => 'permitsRequired',
      'formatter' => 'NullableNumber'
    ),
    array(
      'title' => 'dashboard-table-permit-application-type',
      'name' => 'type',
      'formatter' => function ($row) {
        // TODO: Remove ternary when ECMT permits are removed.
        return isset($row['irhpPermitType']) ?
          $row['irhpPermitType']['name']['description'] :
          $row['permitType']['description'];
      }
    ),
    array(
      'title' => 'dashboard-table-permit-application-status',
      'name' => 'status',
      'formatter' => 'RefDataStatus'
    )
  )
);
