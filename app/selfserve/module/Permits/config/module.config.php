<?php
namespace Permits;

use Permits\Controller\CancelApplicationController;
use Permits\Controller\WithdrawApplicationController;
use Permits\Controller\CheckAnswersController;
use Permits\Controller\FeeController;
use Permits\Controller\OverviewController;
use Permits\Controller\SubmittedController;

return array(
  'controllers' => array(
    'invokables' => array(
      'Permits\Controller\Permits' => 'Permits\Controller\PermitsController',
        CheckAnswersController::class => CheckAnswersController::class,
        OverviewController::class => OverviewController::class,
        FeeController::class => FeeController::class,
        SubmittedController::class => SubmittedController::class,
        CancelApplicationController::class => CancelApplicationController::class,
        WithdrawApplicationController::class => WithdrawApplicationController::class
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
                          'controller'    => OverviewController::class,
                          'action'        => 'generic',
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
              'ecmt-add-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/ecmt-add-licence[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'add',
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
                          'controller'    => CheckAnswersController::class,
                          'action'        => 'generic',
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
                          'controller'    => FeeController::class,
                          'action'        => 'generic',
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
                          'controller'    => SubmittedController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-under-consideration' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-under-consideration[/]',
                      'defaults' => [
                          'controller'    => 'Permits\Controller\Permits',
                          'action'        => 'underConsideration',
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
                          'controller'    => CancelApplicationController::class,
                          'action'        => 'cancel',
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
                                  'controller'    => CancelApplicationController::class,
                                  'action'        => 'confirmation',
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
                            'controller' => WithdrawApplicationController::class,
                            'action' => 'withdraw'
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
                                    'controller'    => WithdrawApplicationController::class,
                                    'action'        => 'confirmation',
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
    /** @todo we don't need all of these different link helpers! OLCS-21512 */
    'view_helpers' => [
        'invokables' => [
            'ecmtSection' => \Permits\View\Helper\EcmtSection::class,
            'permitsBackLink' => \Permits\View\Helper\BackToOverview::class,
            'permitsDashboardLink' => \Permits\View\Helper\PermitsDashboardLink::class,
            'changeAnswerLink' => \Permits\View\Helper\ChangeAnswerLink::class,
            'ecmtLicenceData' => \Permits\View\Helper\EcmtLicenceData::class,
            'underConsiderationLink' => \Permits\View\Helper\UnderConsiderationLink::class,

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
