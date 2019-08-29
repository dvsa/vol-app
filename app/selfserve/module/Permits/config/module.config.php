<?php
namespace Permits;

use Permits\Controller\IrhpApplicationDeclarationController;
use Permits\Controller\CancelApplicationController;
use Permits\Controller\ConfirmChangeController;
use Permits\Controller\EmissionsController;
use Permits\Controller\CabotageController;
use Permits\Controller\RoadworthinessController;
use Permits\Controller\FeePartSuccessfulController;
use Permits\Controller\LicenceController;
use Permits\Controller\RestrictedCountriesController;
use Permits\Controller\SectorsController;
use Permits\Controller\ValidPermitsController;
use Permits\Controller\WithdrawApplicationController;
use Permits\Controller\CheckAnswersController;
use Permits\Controller\DeclarationController;
use Permits\Controller\FeeController;
use Permits\Controller\OverviewController;
use Permits\Controller\DeclineController;
use Permits\Controller\SubmittedController;
use Permits\Controller\PermitsController;
use Permits\Controller\TypeController;
use Permits\Controller\IrhpApplicationController;
use Permits\Controller\IrhpApplicationCountryController;
use Permits\Controller\IrhpApplicationFeeController;
use Permits\Controller\IrhpUnderConsiderationController;
use Permits\Controller\NoOfPermitsController;
use Permits\Controller\IrhpCheckAnswersController;
use Permits\Controller\CancelIrhpApplicationController;
use Permits\Controller\IrhpWithdrawController;
use Permits\Controller\IrhpAwaitingFeeController;
use Permits\Controller\IrhpDeclineController;
use Permits\Controller\IrhpValidPermitsController;
use Permits\Controller\IrhpWindowClosedController;
use Permits\Controller\QaController;
use Permits\Controller\QaControllerFactory;
use Permits\Controller\YearController;
use Permits\Data\Mapper;

return [
  'controllers' => [
    'invokables' => [
        PermitsController::class => PermitsController::class,
        ConfirmChangeController::class => ConfirmChangeController::class,
        LicenceController::class => LicenceController::class,
        TypeController::class => TypeController::class,
        EmissionsController::class => EmissionsController::class,
        CabotageController::class => CabotageController::class,
        RoadworthinessController::class => RoadworthinessController::class,
        SectorsController::class => SectorsController::class,
        CheckAnswersController::class => CheckAnswersController::class,
        DeclarationController::class => DeclarationController::class,
        OverviewController::class => OverviewController::class,
        ValidPermitsController::class => ValidPermitsController::class,
        FeeController::class => FeeController::class,
        FeePartSuccessfulController::class => FeePartSuccessfulController::class,
        DeclineController::class => DeclineController::class,
        SubmittedController::class => SubmittedController::class,
        CancelApplicationController::class => CancelApplicationController::class,
        WithdrawApplicationController::class => WithdrawApplicationController::class,
        IrhpApplicationController::class => IrhpApplicationController::class,
        IrhpApplicationCountryController::class => IrhpApplicationCountryController::class,
        NoOfPermitsController::class => NoOfPermitsController::class,
        IrhpApplicationDeclarationController::class => IrhpApplicationDeclarationController::class,
        IrhpCheckAnswersController::class => IrhpCheckAnswersController::class,
        CancelIrhpApplicationController::class => CancelIrhpApplicationController::class,
        IrhpWithdrawController::class => IrhpWithdrawController::class,
        IrhpAwaitingFeeController::class => IrhpAwaitingFeeController::class,
        IrhpDeclineController::class => IrhpDeclineController::class,
        IrhpApplicationFeeController::class => IrhpApplicationFeeController::class,
        IrhpUnderConsiderationController::class => IrhpUnderConsiderationController::class,
        IrhpValidPermitsController::class => IrhpValidPermitsController::class,
        IrhpWindowClosedController::class => IrhpWindowClosedController::class,
        RestrictedCountriesController::class => RestrictedCountriesController::class,
        YearController::class => YearController::class,
    ],
    'factories' => [
        QaController::class => QaControllerFactory::class,
    ],
  ],
  'router' => [
    'routes' => [
      'permits' => [
        'type'    => 'segment',
        'options' => [
          'route'    => '/permits',
          'defaults' => [
            'controller'    => PermitsController::class,
            'action'        => 'index',
          ],
        ],
          'may_terminate' => true,
          'child_routes' => [
              'valid' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/valid/:licence/type/:type[/]',
                      'defaults' => [
                          'controller'    => IrhpValidPermitsController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'licence' => '[0-9]+',
                          'type' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'application' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/application/:id[/]',
                      'defaults' => [
                          'controller'    => IrhpApplicationController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => true,
                  'child_routes' => [
                      'question' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => ':slug[/]',
                              'defaults' => [
                                  'controller'    => QaController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'slug' => '[0-9A-Za-z\-]+',
                              ],
                          ],
                          'may_terminate' => true,
                          'priority' => -1,
                      ],
                      'licence' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'licence[/]',
                              'defaults' => [
                                  'controller'    => LicenceController::class,
                                  'action'        => 'question',
                              ],
                          ],
                          'may_terminate' => true,
                          'child_routes' => [
                              'change' => [
                                  'type'    => 'segment',
                                  'options' => [
                                      'route'    => 'change[/[:licence]]',
                                      'defaults' => [
                                          'controller'    => ConfirmChangeController::class,
                                          'action'        => 'question',
                                      ],
                                      'constraints' => [
                                          'licence' => '[0-9]+',
                                      ],
                                  ],
                                  'may_terminate' => true,
                              ],
                          ]
                      ],
                      'countries' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'countries[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationCountryController::class,
                                  'action'        => 'question',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'no-of-permits' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'no-of-permits[/]',
                              'defaults' => [
                                  'controller'    => NoOfPermitsController::class,
                                  'action'        => 'question',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'check-answers' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'check-answers[/]',
                              'defaults' => [
                                  'controller'    => IrhpCheckAnswersController::class,
                                  'action'        => 'generic',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'declaration' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'declaration[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationDeclarationController::class,
                                  'action'        => 'question',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'fee' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'fee[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationFeeController::class,
                                  'action'        => 'generic',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'payment' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'payment[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationFeeController::class,
                                  'action'        => 'payment',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'payment-result' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'payment-result[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationFeeController::class,
                                  'action'        => 'paymentResult',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'submitted' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'submitted[/]',
                              'defaults' => [
                                  'controller' => SubmittedController::class,
                                  'action' => 'irhp-submitted',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'under-consideration' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'under-consideration[/]',
                              'defaults' => [
                                  'controller'    => IrhpUnderConsiderationController::class,
                                  'action'        => 'generic',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'awaiting-fee' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'awaiting-fee[/]',
                              'defaults' => [
                                  'controller'    => IrhpAwaitingFeeController::class,
                                  'action'        => 'generic',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'cancel' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'cancel[/]',
                              'defaults' => [
                                  'controller'    => CancelIrhpApplicationController::class,
                                  'action'        => 'cancel',
                              ]
                          ],
                          'may_terminate' => true,
                          'child_routes' => [
                              'confirmation' => [
                                  'type'    => 'segment',
                                  'options' => [
                                      'route'    => 'confirmation[/]',
                                      'defaults' => [
                                          'controller'    => CancelIrhpApplicationController::class,
                                          'action'        => 'confirmation',
                                      ]
                                  ],
                                  'may_terminate' => false,
                              ],
                          ],
                      ],
                      'withdraw' => [
                            'type' => 'segment',
                            'options' => [
                                'route' => 'withdraw[/]',
                                'defaults' => [
                                    'controller'    => IrhpWithdrawController::class,
                                    'action'        => 'withdraw',
                                ],
                            ],
                            'may_terminate' => true,
                            'child_routes' => [
                                'confirmation' => [
                                    'type'    => 'segment',
                                    'options' => [
                                        'route'    => 'confirmation[/]',
                                        'defaults' => [
                                            'controller'    => IrhpWithdrawController::class,
                                            'action'        => 'confirmation',
                                        ],
                                    ],
                                    'may_terminate' => false,
                                ],
                            ],
                      ],
                      'decline' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'decline[/]',
                              'defaults' => [
                                  'controller'    => IrhpDeclineController::class,
                                  'action'        => 'withdraw',
                              ],
                          ],
                          'may_terminate' => true,
                          'child_routes' => [
                              'confirmation' => [
                                  'type'    => 'segment',
                                  'options' => [
                                      'route'    => 'confirmation[/]',
                                      'defaults' => [
                                          'controller'    => IrhpDeclineController::class,
                                          'action'        => 'confirmation',
                                      ],
                                  ],
                                  'may_terminate' => false,
                              ],
                          ],
                      ],
                  ],
              ],
              'ecmt-guidance' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/ecmt-guidance[/]',
                      'defaults' => [
                          'controller'    => PermitsController::class,
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
              'type' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/type[/]',
                      'defaults' => [
                          'controller'    => TypeController::class,
                          'action'        => 'question',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'year' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/year/:type[/]',
                      'defaults' => [
                          'controller'    => YearController::class,
                          'action'        => 'question',
                      ],
                      'constraints' => [
                          'type' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'window-closed' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/window-closed[/]',
                      'defaults' => [
                          'controller'    => IrhpWindowClosedController::class,
                          'action'        => 'generic',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'add-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/type/:type/licence/add[/:year][/]',
                      'defaults' => [
                          'controller'    => LicenceController::class,
                          'action'        => 'add',
                      ],
                      'constraints' => [
                          'type' => '[0-9]+',
                          'year' => '\d{4}'
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '[/:id]/licence[/]',
                      'defaults' => [
                          'controller'    => LicenceController::class,
                          'action'        => 'question-ecmt',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'change-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/change-licence[/[:licence]]',
                      'defaults' => [
                          'controller'    => ConfirmChangeController::class,
                          'action'        => 'ecmt',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                          'licence' => '[0-9]+'
                      ],
                  ],
                  'may_terminate' => true,
              ],
              'ecmt-emissions' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-emissions[/]',
                      'defaults' => [
                          'controller'    => EmissionsController::class,
                          'action'        => 'question',
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
                          'controller'    => CabotageController::class,
                          'action'        => 'question',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-roadworthiness' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-roadworthiness[/]',
                      'defaults' => [
                          'controller'    => RoadworthinessController::class,
                          'action'        => 'question',
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
                          'controller'    => RestrictedCountriesController::class,
                          'action'        => 'question',
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
                          'controller'    => PermitsController::class,
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
                          'controller'    => PermitsController::class,
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
                          'controller'    => SectorsController::class,
                          'action'        => 'question',
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
                          'controller'    => PermitsController::class,
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
                          'controller'    => DeclarationController::class,
                          'action'        => 'question',
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
              'payment-result' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/payment-result[/]',
                      'defaults' => [
                          'controller'    => FeeController::class,
                          'action'        => 'paymentResult',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-payment' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-payment[/]',
                      'defaults' => [
                          'controller'    => PermitsController::class,
                          'action'        => 'payment',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'print-receipt' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/print-receipt[/]:reference',
                      'defaults' => [
                          'controller'    => PermitsController::class,
                          'action'        => 'print',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'application-submitted' => [
                  'type' => 'segment',
                  'options' => [
                      'route' => '/:id/application-submitted',
                      'defaults' => [
                          'controller' => SubmittedController::class,
                          'action' => 'application-submitted',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'issue-submitted' => [
                  'type' => 'segment',
                  'options' => [
                      'route' => '/:id/issue-submitted',
                      'defaults' => [
                          'controller' => SubmittedController::class,
                          'action' => 'issue-submitted',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'ecmt-valid-permits' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/ecmt-valid-permits/:licence[/]',
                    'defaults' => [
                        'controller'    => ValidPermitsController::class,
                        'action'        => 'valid',
                    ],
                    'constraints' => [
                        'licence' => '[0-9]+',
                    ],
                ],
                'may_terminate' => false,
              ],
              'ecmt-unpaid-permits' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-unpaid-permits[/]',
                      'defaults' => [
                          'controller'    => ValidPermitsController::class,
                          'action'        => 'unpaid',
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
                          'controller'    => PermitsController::class,
                          'action'        => 'underConsideration',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'cancel-application' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/cancel-application[/]',
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
              'withdraw-application' => [
                    'type' => 'segment',
                    'options' => [
                        'route' => '/:id/withdraw-application[/]',
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
              ],
              'ecmt-awaiting-fee' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-awaiting-fee[/]',
                      'defaults' => [
                          'controller'    => FeePartSuccessfulController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => true,
                  'child_routes' => [
                      'decline' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'decline[/]',
                              'defaults' => [
                                  'controller'    => DeclineController::class,
                                  'action'        => 'decline',
                              ],
                          ],
                          'may_terminate' => true,
                          'child_routes' => [
                              'confirmation' => [
                                  'type'    => 'segment',
                                  'options' => [
                                      'route'    => 'confirmation[/]',
                                      'defaults' => [
                                          'controller'    => DeclineController::class,
                                          'action'        => 'confirmation',
                                      ],
                                  ],
                                  'may_terminate' => false,
                              ],
                          ],
                      ],
                  ],
              ],
          ],
      ],
    ],
  ],
  'service_manager' => [
      'invokables' => [
          Mapper\ApplicationFees::class => Mapper\ApplicationFees::class,
          Mapper\ValidEcmtPermitConstrainedCountries::class => Mapper\ValidEcmtPermitConstrainedCountries::class,
          Mapper\ValidEcmtPermits::class => Mapper\ValidEcmtPermits::class,
          Mapper\AvailableCountries::class => Mapper\AvailableCountries::class,
          Mapper\AvailableTypes::class => Mapper\AvailableTypes::class,
          Mapper\LicencesAvailable::class => Mapper\LicencesAvailable::class,
          Mapper\RestrictedCountries::class => Mapper\RestrictedCountries::class,
          Mapper\Sectors::class => Mapper\Sectors::class,
      ],
      'factories' => [
          Mapper\AcceptOrDeclinePermits::class => Mapper\AcceptOrDeclinePermitsFactory::class,
          Mapper\CheckAnswers::class => Mapper\CheckAnswersFactory::class,
          Mapper\EcmtNoOfPermits::class => Mapper\EcmtNoOfPermitsFactory::class,
          Mapper\FeeList::class => Mapper\FeeListFactory::class,
          Mapper\IrhpApplicationFeeSummary::class => Mapper\IrhpApplicationFeeSummaryFactory::class,
          Mapper\IrhpCheckAnswers::class => Mapper\IrhpCheckAnswersFactory::class,
          Mapper\AvailableYears::class => Mapper\AvailableYearsFactory::class,
          Mapper\ChangeLicence::class => Mapper\ChangeLicenceFactory::class,
          Mapper\NoOfPermits::class => Mapper\NoOfPermitsFactory::class,
      ],
  ],
    /** @todo we don't need all of these different link helpers! OLCS-21512 */
    'view_helpers' => [
        'invokables' => [
            'ecmtSection' => \Permits\View\Helper\EcmtSection::class,
            'irhpApplicationSection' => \Permits\View\Helper\IrhpApplicationSection::class,
            'permitsBackLink' => \Permits\View\Helper\BackToOverview::class,
            'saveAndReturnLink' => \Permits\View\Helper\BackToOverview::class,
            'permitsDashboardLink' => \Permits\View\Helper\PermitsDashboardLink::class,
            'link' => \Permits\View\Helper\Link::class,
            'answerFormatter' => \Permits\View\Helper\AnswerFormatter::class,
            'underConsiderationLink' => \Permits\View\Helper\UnderConsiderationLink::class,
        ],
    ],
  'view_manager' => [
    'template_path_stack' => [
      'permits' => __DIR__ . '/../view',
    ],
  ],
  'tables' => [
    'config' => [
      __DIR__ . '/../src/Permits/Table/Tables/'
    ]
  ],
];
