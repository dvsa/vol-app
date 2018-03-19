<?php

return array(
  'variables' => array(
    'title' => 'Freight sectors',
    'titleSingular' => 'Freight sector'
  ),
  'settings' => array(
    'crud' => array(

    ),
    'paginate' => array(
      'limit' => array(
        'default' => 25,
        'options' => array(10, 25, 50)
      ),
    )
  ),
  'columns' => array(
    array(
      'title' => 'ID',
      'name' => 'sectorId'
    ),
    array(
      'title' => 'Commodity',
      'name' => 'sectorName'
    ),
    array(
      'title' => 'Proportion (%)',
      'name' => 'siftingPercentage'
    ),

    array(
      'title' => 'Number of permits',
      'name' => 'allocatedPermits'
    ),
    array(
      'title' => 'Total received applications',
      'name' => 'applicationsTotal'
    ),
  )
);
