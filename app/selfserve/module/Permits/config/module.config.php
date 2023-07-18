<?php

namespace Permits;

use Permits\Controller\CancelIrhpApplicationControllerFactory;
use Permits\Controller\EssentialInformationControllerFactory;
use Permits\Controller\IrhpApplicationControllerFactory;
use Permits\Controller\IrhpApplicationCountryConfirmationControllerFactory;
use Permits\Controller\IrhpApplicationCountryControllerFactory;
use Permits\Controller\IrhpApplicationDeclarationController;
use Permits\Controller\IrhpApplicationDeclarationControllerFactory;
use Permits\Controller\IrhpApplicationFeeControllerFactory;
use Permits\Controller\IrhpApplicationPeriodController;
use Permits\Controller\IrhpApplicationPeriodControllerFactory;
use Permits\Controller\IrhpAwaitingFeeControllerFactory;
use Permits\Controller\IrhpCandidatePermitSelectionControllerFactory;
use Permits\Controller\IrhpCheckAnswersControllerFactory;
use Permits\Controller\IrhpDeclineControllerFactory;
use Permits\Controller\IrhpNoLicencesControllerFactory;
use Permits\Controller\IrhpNotEligibleControllerFactory;
use Permits\Controller\IrhpPermitAppCheckAnswersControllerFactory;
use Permits\Controller\IrhpPermitsExhaustedControllerFactory;
use Permits\Controller\IrhpStockControllerFactory;
use Permits\Controller\IrhpUnderConsiderationControllerFactory;
use Permits\Controller\IrhpUnpaidPermitsControllerFactory;
use Permits\Controller\IrhpValidPermitsControllerFactory;
use Permits\Controller\IrhpWithdrawControllerFactory;
use Permits\Controller\LicenceController;
use Permits\Controller\LicenceControllerFactory;
use Permits\Controller\MaxPermittedReachedForStockControllerFactory;
use Permits\Controller\MaxPermittedReachedForTypeControllerFactory;
use Permits\Controller\NoOfPermitsControllerFactory;
use Permits\Controller\PermitsControllerFactory;
use Permits\Controller\SubmittedController;
use Permits\Controller\PermitsController;
use Permits\Controller\SubmittedControllerFactory;
use Permits\Controller\TypeController;
use Permits\Controller\IrhpApplicationController;
use Permits\Controller\IrhpApplicationCountryController;
use Permits\Controller\IrhpApplicationCountryConfirmationController;
use Permits\Controller\IrhpApplicationFeeController;
use Permits\Controller\IrhpCandidatePermitSelectionController;
use Permits\Controller\IrhpUnderConsiderationController;
use Permits\Controller\NoOfPermitsController;
use Permits\Controller\IrhpCheckAnswersController;
use Permits\Controller\IrhpPermitAppCheckAnswersController;
use Permits\Controller\CancelIrhpApplicationController;
use Permits\Controller\IrhpWithdrawController;
use Permits\Controller\IrhpAwaitingFeeController;
use Permits\Controller\IrhpDeclineController;
use Permits\Controller\IrhpUnpaidPermitsController;
use Permits\Controller\IrhpValidPermitsController;
use Permits\Controller\IrhpPermitsExhaustedController;
use Permits\Controller\IrhpNotEligibleController;
use Permits\Controller\IrhpNoLicencesController;
use Permits\Controller\MaxPermittedReachedForStockController;
use Permits\Controller\MaxPermittedReachedForTypeController;
use Permits\Controller\QaController;
use Permits\Controller\QaControllerFactory;
use Permits\Controller\TypeControllerFactory;
use Permits\Controller\WindowClosedControllerFactory;
use Permits\Controller\YearController;
use Permits\Controller\WindowClosedController;
use Permits\Controller\IrhpStockController;
use Permits\Controller\EssentialInformationController;
use Permits\Controller\YearControllerFactory;
use Permits\Data\Mapper;

return [
  'controllers' => [
    'factories' => [
        LicenceController::class => LicenceControllerFactory::class,
        TypeController::class => TypeControllerFactory::class,
        SubmittedController::class => SubmittedControllerFactory::class,
        IrhpApplicationController::class => IrhpApplicationControllerFactory::class,
        IrhpApplicationCountryController::class => IrhpApplicationCountryControllerFactory::class,
        IrhpApplicationCountryConfirmationController::class => IrhpApplicationCountryConfirmationControllerFactory::class,
        NoOfPermitsController::class => NoOfPermitsControllerFactory::class,
        IrhpCheckAnswersController::class => IrhpCheckAnswersControllerFactory::class,
        IrhpPermitAppCheckAnswersController::class => IrhpPermitAppCheckAnswersControllerFactory::class,
        CancelIrhpApplicationController::class => CancelIrhpApplicationControllerFactory::class,
        IrhpWithdrawController::class => IrhpWithdrawControllerFactory::class,
        IrhpAwaitingFeeController::class => IrhpAwaitingFeeControllerFactory::class,
        IrhpDeclineController::class => IrhpDeclineControllerFactory::class,
        IrhpApplicationFeeController::class => IrhpApplicationFeeControllerFactory::class,
        IrhpUnderConsiderationController::class => IrhpUnderConsiderationControllerFactory::class,
        IrhpUnpaidPermitsController::class => IrhpUnpaidPermitsControllerFactory::class,
        IrhpValidPermitsController::class => IrhpValidPermitsControllerFactory::class,
        IrhpNotEligibleController::class => IrhpNotEligibleControllerFactory::class,
        IrhpNoLicencesController::class => IrhpNoLicencesControllerFactory::class,
        MaxPermittedReachedForStockController::class => MaxPermittedReachedForStockControllerFactory::class,
        MaxPermittedReachedForTypeController::class => MaxPermittedReachedForTypeControllerFactory::class,
        YearController::class => YearControllerFactory::class,
        WindowClosedController::class => WindowClosedControllerFactory::class,
        IrhpStockController::class => IrhpStockControllerFactory::class,
        EssentialInformationController::class => EssentialInformationControllerFactory::class,
        IrhpApplicationPeriodController::class => IrhpApplicationPeriodControllerFactory::class,
        IrhpCandidatePermitSelectionController::class => IrhpCandidatePermitSelectionControllerFactory::class,
        IrhpApplicationDeclarationController::class => IrhpApplicationDeclarationControllerFactory::class,
        IrhpPermitsExhaustedController::class => IrhpPermitsExhaustedControllerFactory::class,
        PermitsController::class => PermitsControllerFactory::class,
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
                      'ipa' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'ipa/:irhpPermitApplication[/]',
                               'constraints' => [
                                  'irhpPermitApplication' => '[0-9]+',
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
                              'check-answers' => [
                                  'type'    => 'segment',
                                  'options' => [
                                      'route'    => 'check-answers[/]',
                                      'defaults' => [
                                          'controller'    => IrhpPermitAppCheckAnswersController::class,
                                          'action'        => 'generic',
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
                      'countries-confirmation' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'countries-confirmation[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationCountryConfirmationController::class,
                                  'action'        => 'question',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'essential-information' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'country/:country/essential-information[/]',
                              'defaults' => [
                                  'controller'    => EssentialInformationController::class,
                                  'action'        => 'generic',
                              ],
                              'constraints' => [
                                  'country' => '[A-Z]{2}',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'period' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'country/:country/period[/]',
                              'defaults' => [
                                  'controller'    => IrhpApplicationPeriodController::class,
                                  'action'        => 'question',
                              ],
                              'constraints' => [
                                  'country' => '[A-Z]{2}',
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
                      'unpaid-permits' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'unpaid-permits[/]',
                              'defaults' => [
                                  'controller'    => IrhpUnpaidPermitsController::class,
                                  'action'        => 'generic',
                              ],
                          ],
                          'may_terminate' => false,
                      ],
                      'candidate-selection' => [
                          'type'    => 'segment',
                          'options' => [
                              'route'    => 'candidate-selection[/]',
                              'defaults' => [
                                  'controller'    => IrhpCandidatePermitSelectionController::class,
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
                      'route'    => '/type/:type[/]',
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
              'stock' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/type/:type/year/:year[/]',
                      'defaults' => [
                          'controller'    => IrhpStockController::class,
                          'action'        => 'question',
                      ],
                      'constraints' => [
                          'type' => '[0-9]+',
                          'year' => '\d{4}',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'window-closed' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/window-closed[/]',
                      'defaults' => [
                          'controller'    => WindowClosedController::class,
                          'action'        => 'generic',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'exhausted' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/exhausted[/]',
                      'defaults' => [
                          'controller'    => IrhpPermitsExhaustedController::class,
                          'action'        => 'generic',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'not-eligible' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/not-eligible[/]',
                      'defaults' => [
                          'controller'    => IrhpNotEligibleController::class,
                          'action'        => 'generic',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'no-licences' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/no-licences[/]',
                      'defaults' => [
                          'controller'    => IrhpNoLicencesController::class,
                          'action'        => 'generic',
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'max-permitted-reached-for-type' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/licence/add/:type/max-permitted-reached',
                      'defaults' => [
                          'controller'    => MaxPermittedReachedForTypeController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'type' => '[0-9]+',
                      ],
                  ],
                  'may_terminate' => true,
              ],
              'add-licence' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/licence/add/:type[/:stock][/]',
                      'defaults' => [
                          'controller'    => LicenceController::class,
                          'action'        => 'question',
                      ],
                      'constraints' => [
                          'type' => '[0-9]+',
                          'stock' => '[0-9]+'
                      ],
                  ],
                  'may_terminate' => false,
              ],
              'max-permitted-reached-for-stock' => [
                  'type'    => 'segment',
                  'options' => [
                      'route'    => '/licence/:licence/max-permitted-reached/:type/:stock[/]',
                      'defaults' => [
                          'controller'    => MaxPermittedReachedForStockController::class,
                          'action'        => 'generic',
                      ],
                      'constraints' => [
                          'licence' => '[0-9]+',
                          'type' => '[0-9]+',
                          'stock' => '[0-9]+'
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
          ],
      ],
    ],
  ],
  'service_manager' => [
      'factories' => [
          Mapper\MapperManager::class => Mapper\MapperManagerFactory::class
      ]
  ],
  'mappers' =>  [
      'invokables' => [
          Mapper\AvailableCountries::class => Mapper\AvailableCountries::class,
          Mapper\AvailableTypes::class => Mapper\AvailableTypes::class,
          Mapper\AvailableStocks::class => Mapper\AvailableStocks::class,
          Mapper\LicencesAvailable::class => Mapper\LicencesAvailable::class,
          Mapper\PermitTypeTitle::class => Mapper\PermitTypeTitle::class,
          Mapper\IrhpFee::class => Mapper\IrhpFee::class,
          Mapper\ConfirmedUpdatedCountries::class => Mapper\ConfirmedUpdatedCountries::class,
          Mapper\RemovedCountries::class => Mapper\RemovedCountries::class,
          Mapper\SelectedCandidatePermits::class => Mapper\SelectedCandidatePermits::class,
      ],
      'factories' => [
          Mapper\EcmtNoOfPermits::class => Mapper\EcmtNoOfPermitsFactory::class,
          Mapper\IrhpApplicationFeeSummary::class => Mapper\IrhpApplicationFeeSummaryFactory::class,
          Mapper\NoOfPermits::class => Mapper\NoOfPermitsFactory::class,
          Mapper\AvailableBilateralStocks::class => Mapper\AvailableBilateralStocksFactory::class,
          Mapper\AvailableYears::class => Mapper\AvailableYearsFactory::class,
          Mapper\CandidatePermitSelection::class => Mapper\CandidatePermitSelectionFactory::class,
      ],
    ]
    ,
    /** TODO: we don't need all of these different link helpers! OLCS-21512 */
    'view_helpers' => [
        'invokables' => [
            'irhpApplicationSection' => \Permits\View\Helper\IrhpApplicationSection::class,
            'permitsBackLink' => \Permits\View\Helper\BackToOverview::class,
            'saveAndReturnLink' => \Permits\View\Helper\BackToOverview::class,
            'permitsDashboardLink' => \Permits\View\Helper\PermitsDashboardLink::class,
            'permitsCheckAnswerLink' => \Permits\View\Helper\PermitsCheckAnswerLink::class,
            'answerFormatter' => \Permits\View\Helper\AnswerFormatter::class,
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
