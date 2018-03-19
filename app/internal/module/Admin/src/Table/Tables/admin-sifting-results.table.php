<?php

return array(

  'settings' => array(
    'crud' => array(
      'actions' => array(
        'Automatically allocate' => array('class' => 'action--primary', 'requireRows' => false),
        'Allocate selected' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
        'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
        'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
      )
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
      'title' => 'Permit',
      'name' => 'permitsId',
      'sort' => 'permitsId'
    ),
    array(
      'title' => 'Operator',
      'name' => 'startDate',
      'sort' => 'startDate'
    ),
    array(
      'title' => 'Licence',
      'name' => 'ecmtPermitsApplication',
      'sort' => 'ecmtPermitsApplication'
    ),
    array(
      'title' => 'Intensity of Use',
      'name' => 'intensity',
      'sort' => 'intensity',
    ),

    /*array(
      'title' => 'Italy',
      'name' => '',
      'sort' => '',
    ),
    array(
      'title' => 'Austria',
      'name' => '',
      'sort' => '',
    ),
    array(
      'title' => 'Greece',
      'name' => '',
      'sort' => '',
    ),*/
    array(
      'title' => 'Countries',
      'name' => 'ecmtCountriesIds',
      'sort' => 'ecmtCountriesIds',
    ),
    array(
      'title' => 'Sifting value',
      'name' => 'siftingValue',
      'sort' => 'siftingValue',
    ),
    array(
      'title' => '',
      'width' => 'checkbox',
      'format' => '{{[elements/radio]}}'
    ),
  )
);

/*
ecmtCountriesIds
ecmtPermitsApplicationId
intensity
permitsId
sectorId
siftingRandomFactor
siftingValue
siftingValueRandom
*/