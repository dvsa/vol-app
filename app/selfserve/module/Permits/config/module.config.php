<?php
namespace Permits;

use Permits\Controller\CancelApplicationController;
use Permits\Controller\ConfirmChangeController;
use Permits\Controller\EmissionsController;
use Permits\Controller\CabotageController;
use Permits\Controller\FeePartSuccessfulController;
use Permits\Controller\LicenceController;
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

return [
  'controllers' => [
    'invokables' => [
        PermitsController::class => PermitsController::class,
        ConfirmChangeController::class => ConfirmChangeController::class,
        LicenceController::class => LicenceController::class,
        EmissionsController::class => EmissionsController::class,
        CabotageController::class => CabotageController::class,
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
        WithdrawApplicationController::class => WithdrawApplicationController::class
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
              'ecmt-short' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/ecmt-short'
                  ],
                  'may_terminate' => true,
                  'child_routes' => [
                      'overview' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/overview[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'emissions' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/emissions[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'cabotage' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/cabotage[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'countries' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/countries[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'startdate' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/startdate[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'enddate' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/enddate[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'urgency' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/urgency[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'benefit' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/benefit[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'options' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/options[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'criteria' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/criteria[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'supporting' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/supporting[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'check-answers' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/check-answers[/]',
                              'defaults' => [
                                  'controller'    => CheckAnswersController::class,
                                  'action'        => 'generic',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'declaration' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/declaration[/]',
                              'defaults' => [
                                  'controller'    => DeclarationController::class,
                                  'action'        => 'question',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'fee' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:shortId/fee[/]',
                              'defaults' => [
                                  'controller'    => FeeController::class,
                                  'action'        => 'generic',
                              ],
                              'constraints' => [
                                  'shortId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                  ],
              ],
              'ecmt-removal' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/ecmt-removal'
                  ],
                  'may_terminate' => true,
                  'child_routes' => [
                      'overview' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/overview[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'cabotage' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/cabotage[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'number' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/number[/]',
                              'defaults' => [
                                  'controller'    => PermitsController::class,
                                  'action'        => 'index',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'check-answers' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/check-answers[/]',
                              'defaults' => [
                                  'controller'    => CheckAnswersController::class,
                                  'action'        => 'generic',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'declaration' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/declaration[/]',
                              'defaults' => [
                                  'controller'    => DeclarationController::class,
                                  'action'        => 'question',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'fee' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => '/:removalId/fee[/]',
                              'defaults' => [
                                  'controller'    => FeeController::class,
                                  'action'        => 'generic',
                              ],
                              'constraints' => [
                                  'removalId' => '[0-9]+',
                              ],
                          ],
                          'may_terminate' => false,
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
              'licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '[/:id]/licence[/]',
                      'defaults' => [
                          'controller'    => LicenceController::class,
                          'action'        => 'question',
                      ],
                      'constraints' => [
                          'id' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'add-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/licence/add[/]',
                      'defaults' => [
                          'controller'    => LicenceController::class,
                          'action'        => 'add',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'change-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/change-licence[/]',
                      'defaults' => [
                          'controller'    => ConfirmChangeController::class,
                          'action'        => 'question',
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
              'ecmt-countries' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-countries[/]',
                      'defaults' => [
                          'controller'    => PermitsController::class,
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
              'ecmt-print-receipt' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/ecmt-print-receipt[/]:reference',
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
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/application-submitted[/]',
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
                'ecmt-fee-submitted' => [
                    'type'    => 'segment',
                    'options' => [
                        'route'    => '/:id/ecmt-fee-submitted[/]',
                        'defaults' => [
                            'controller'    => SubmittedController::class,
                            'action'        => 'fee-submitted',
                        ],
                        'constraints' => [
                            'id' => '[0-9]+',
                        ],
                    ],
                    'may_terminate' => false,
                ],
              'ecmt-decline-submitted' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/:id/ecmt-decline-submitted[/]',
                    'defaults' => [
                        'controller'    => SubmittedController::class,
                        'action'        => 'decline',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
                'may_terminate' => false,
              ],
              'fee-waived-application-submitted' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/:id/fee-waived-application-submitted[/]',
                      'defaults' => [
                          'controller' => SubmittedController::class,
                          'action'     => 'fee-waived',
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
                    'route'    => '/:id/ecmt-valid-permits[/]',
                    'defaults' => [
                        'controller'    => ValidPermitsController::class,
                        'action'        => 'valid',
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
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
    /** @todo we don't need all of these different link helpers! OLCS-21512 */
    'view_helpers' => [
        'invokables' => [
            'ecmtSection' => \Permits\View\Helper\EcmtSection::class,
            'permitsBackLink' => \Permits\View\Helper\BackToOverview::class,
            'saveAndReturnLink' => \Permits\View\Helper\BackToOverview::class,
            'permitsDashboardLink' => \Permits\View\Helper\PermitsDashboardLink::class,
            'changeAnswerLink' => \Permits\View\Helper\ChangeAnswerLink::class,
            'ecmtLicenceData' => \Permits\View\Helper\EcmtLicenceData::class,
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
