<?php
namespace Permits;

return array(
  'controllers' => array(
    'invokables' => array(
      'Permits\Controller\Permits' => 'Permits\Controller\PermitsController',
    ),
  ),

  'router' => array(
    'routes' => array(
      'permits' => array(
        'type'    => 'segment',
        'options' => array(
          'route'    => '/permits[/][:action][/:id]',
          'constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
          ),
          'defaults' => array(
            'controller'    => 'Permits\Controller\Permits',
            'action'        => 'index',
          ),
        ),
      ),
    ),
  ),

  'view_manager' => array(
    'template_path_stack' => array(
      'permits' => __DIR__ . '/../view',
    ),
  ),
  'tables' => array(
    'config' => array(
      __DIR__ . '/../src/Permits/Table/Tables/'
    )
  ),
);
