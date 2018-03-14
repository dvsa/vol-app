<?php

return array(
  'variables' => array(
    'title' => 'Sifting results for sector 01. Food products, beverages and tobacco',
    'titleSingular' => 'parameter'
  ),
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
      'title' => 'Permit?',
      'name' => '',
      'sort' => 'id'
    ),
    array(
      'title' => 'Operator',
      'name' => '',
      'sort' => ''
    ),
    array(
      'title' => 'Intensity of Use',
      'name' => '',
      'sort' => '',
    ),

    array(
      'title' => 'Italy',
      'width' => '',
      'format' => ''
    ),
    array(
      'title' => 'Austria',
      'width' => '',
      'format' => ''
    ),
    array(
      'title' => 'Greece',
      'width' => '',
      'format' => ''
    ),
    array(
      'title' => 'License no',
      'width' => '',
      'format' => ''
    ),
    array(
      'title' => 'Sifting value',
      'width' => '',
      'format' => ''
    ),
    array(
      'title' => '',
      'width' => 'checkbox',
      'format' => '{{[elements/radio]}}'
    ),
  )
);
