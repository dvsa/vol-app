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
          'route'    => '/permits',
          'defaults' => array(
            'controller'    => 'Permits\Controller\Permits',
            'action'        => 'index',
          ),
        ),
          'may_terminate' => true,
          'child_routes' => [
              'ecmt-guidance' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/ecmt-guidance[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'ecmtGuidance',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'application-overview' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/application-overview[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'applicationOverview',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '[/:id]/ecmt-licence[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'ecmtLicence',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-change-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-change-licence[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'changeLicence',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => true,
              ],
              'ecmt-euro6' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-euro6[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'euro6Emissions',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-cabotage' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-cabotage[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'cabotage',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-countries' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-countries[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'restrictedCountries',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-trips' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-trips[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'trips',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-international-journey' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-international-journey[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'internationalJourney',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-sectors' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-sectors[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'sector',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-no-of-permits' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-no-of-permits[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'permitsRequired',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-check-answers' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-check-answers[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'checkAnswers',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-declaration' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-declaration[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'declaration',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-fee' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-fee[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'fee',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-submitted' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-submitted[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'submitted',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-cancel-application' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-cancel-application[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'cancelApplication',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => true,
                  'child_routes' => [
                      'confirmation' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'confirmation[/]',
                              'defaults' => [
                                  'controller'    => 'Permits\Controller\Permits',
                                  'action'        => 'cancelConfirmation',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                  ],
              ],
              'ecmt-withdraw-application' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/:id/ecmt-withdraw-application[/]',
                        'defaults' => [
                            'controller' => 'Permits\Controller\Permits',
                            'action' => 'withdrawApplication'
                        ],
                        'constraints' => [
                            'id' => '[0-9]+',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'confirmation' => [
                            'type'    => 'segment',
                            'options' => [
                                'route'    => 'confirmation[/]',
                                'defaults' => [
                                    'controller'    => 'Permits\Controller\Permits',
                                    'action'        => 'withdrawConfirmation',
                                ],
                            ],
                            'may_terminate' => false,
                        ],
                    ],
                ]
          ],
      ),
    ),
  ),
    'view_helpers' => [
        'invokables' => [
            'ecmtSection' => \Permits\View\Helper\EcmtSection::class,
            'permitsBackLink' => \Permits\View\Helper\BackToOverview::class,
            'changeAnswerLink' => \Permits\View\Helper\ChangeAnswerLink::class,
            'ecmtLicenceTitle' => \Permits\View\Helper\EcmtLicenceTitle::class,
        ],
    ],
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
