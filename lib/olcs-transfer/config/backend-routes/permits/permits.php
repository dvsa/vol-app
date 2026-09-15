<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'permits' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'permits[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'report' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'report[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReportList::class),
                ]
            ],
            'queue-report' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'queue-report[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Permits\QueueReport::class),
                ]
            ],
            'irhp-permits-accept' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'irhp-permits-accept[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Permits\AcceptIrhpPermits::class),
                ]
            ],
            'queue-run-scoring' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'queue-run-scoring[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Permits\QueueRunScoring::class),
                ]
            ],
            'queue-accept-scoring' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'queue-accept-scoring[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Permits\QueueAcceptScoring::class),
                ]
            ],
            'ecmt-permit-fees' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ecmt-permit-fees[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\EcmtPermitFees::class),
                ]
            ],
            'available-types' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-types[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\AvailableTypes::class),
                ]
            ],
            'available-years' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-years[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\AvailableYears::class),
                ]
            ],
            'available-stocks' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-stocks[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\AvailableStocks::class),
                ]
            ],
            'emissions-by-year' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'emissions-by-year[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\EmissionsByYear::class),
                ]
            ],
            'open-windows' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'open-windows[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\OpenWindows::class),
                ]
            ],
            'stock-operations-permitted' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'stock-operations-permitted[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\StockOperationsPermitted::class),
                ]
            ],
            'stock-alignment-report' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'stock-alignment-report[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\StockAlignmentReport::class),
                ]
            ],
            'post-scoring-report' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'post-scoring-report[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\PostScoringReport::class),
                ]
            ],
            'ready-to-print-type' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print-type[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrintType::class),
                ]
            ],
            'ready-to-print-country' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print-country[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrintCountry::class),
                ]
            ],
            'ready-to-print-stock' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print-stock[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrintStock::class),
                ]
            ],
            'ready-to-print-range-type' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print-range-type[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrintRangeType::class),
                ]
            ],
            'ready-to-print' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrint::class),
                ]
            ],
            'ready-to-print-confirm' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'ready-to-print-confirm[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\ReadyToPrintConfirm::class),
                ]
            ],
            'print' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'print[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Permits\PrintPermits::class),
                ]
            ],
            'max-permitted-reached-by-type-and-organisation' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'max-permitted-reached-by-type-and-organisation[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\MaxPermittedReachedByTypeAndOrganisation::class),
                ]
            ],
            'max-permitted-reached-by-stock-and-licence' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'max-permitted-reached-by-stock-and-licence[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Permits\MaxPermittedReachedByStockAndLicence::class),
                ]
            ],
        ]
    ]
];
