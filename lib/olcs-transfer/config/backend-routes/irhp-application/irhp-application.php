<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-application' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-application[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\ById::class),
                    'countries' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'countries[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateCountries::class),
                        ]
                    ],
                    'period' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'period[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdatePeriod::class),
                        ]
                    ],
                    'question-answer' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'question-answer[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\IrhpApplication\QuestionAnswer::class),
                        ]
                    ],
                    'available-licences' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'available-licences[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\IrhpApplication\AvailableLicences::class),
                        ],
                    ],
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\IrhpApplication\Create::class),
            'update-multiple-no-of-permits' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'update-multiple-no-of-permits[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateMultipleNoOfPermits::class),
                ],
            ],
            'update-declaration' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'update-declaration[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateDeclaration::class),
                ],
            ],
            'update-check-answers' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'update-check-answers[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateCheckAnswers::class),
                ]
            ],
            'submit-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'submit-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\SubmitApplication::class),
                ]
            ],
            'max-stock-permits' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'max-stock-permits[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\MaxStockPermits::class),
                ]
            ],
            'max-stock-permits-by-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'max-stock-permits-by-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\MaxStockPermitsByApplication::class),
                ]
            ],
            'fee-breakdown' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'fee-breakdown[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\FeeBreakdown::class),
                ]
            ],
            'fee-per-permit' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'fee-per-permit[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\FeePerPermit::class),
                ]
            ],
            'internal-applications-summary' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'internal-applications-summary[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\InternalApplicationsSummary::class),
                ]
            ],
            'selfserve-applications-summary' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'selfserve-applications-summary[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\SelfserveApplicationsSummary::class),
                ]
            ],
            'selfserve-issued-permits-summary' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'selfserve-issued-permits-summary[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\SelfserveIssuedPermitsSummary::class),
                ]
            ],
            'bilateral-metadata' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'bilateral-metadata[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\BilateralMetadata::class),
                ]
            ],
            'bilateral-country-accessible' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'bilateral-country-accessible[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\BilateralCountryAccessible::class),
                ]
            ],
            'cancel-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'cancel-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\IrhpApplication\CancelApplication::class),
                ]
            ],
            'terminate' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'terminate[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\IrhpApplication\Terminate::class),
                ]
            ],
            'withdraw' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'withdraw[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\IrhpApplication\Withdraw::class),
                ],
            ],
            'revive-from-withdrawn' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'revive-from-withdrawn[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\ReviveFromWithdrawn::class),
                ],
            ],
            'revive-from-unsuccessful' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'revive-from-unsuccessful[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\ReviveFromUnsuccessful::class),
                ],
            ],
            'grant' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'grant[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\Grant::class),
                ],
            ],
            'reset-to-not-yet-submitted-from-valid' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'reset-to-not-yet-submitted-from-valid[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\ResetToNotYetSubmittedFromValid::class),
                ],
            ],
            'reset-to-not-yet-submitted-from-cancelled' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'reset-to-not-yet-submitted-from-cancelled[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\ResetToNotYetSubmittedFromCancelled::class),
                ],
            ],
            'full' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'full[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\IrhpApplication\CreateFull::class),
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateFull::class),
                ]
            ],
            'application-step' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application-step[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\ApplicationStep::class),
                ],
            ],
            'submit-application-step' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'submit-application-step[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\SubmitApplicationStep::class),
                ],
            ],
            'application-path' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application-path[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\ApplicationPath::class),
                ],
            ],
            'submit-application-path' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'submit-application-path[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\SubmitApplicationPath::class),
                ],
            ],
            'documents' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'documents[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\Documents::class),
                ],
            ],
            'update-candidate-permit-selection' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'update-candidate-permit-selection[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpApplication\UpdateCandidatePermitSelection::class),
                ],
            ],
            'answers-summary' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'answers-summary[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\AnswersSummary::class),
                ],
            ],
            'permits-available' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'permits-available[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\PermitsAvailable::class),
                ]
            ],
            'application-path-list' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'application-path-list[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\ApplicationPathGroupList::class),
                ]
            ],
            'ranges-by-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ranges-by-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\RangesByIrhpApplication::class),
                ]
            ],
            'get-grantability' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'get-grantability[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpApplication\GetGrantability::class),
                ]
            ],
        ]
    ],
];
