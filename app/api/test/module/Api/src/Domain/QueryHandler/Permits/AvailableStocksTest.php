<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\AvailableStocks;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks as AvailableStocksQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use DateTime;

class AvailableStocksTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AvailableStocks();
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityStockAvailabilityChecker' => m::mock(StockAvailabilityChecker::class)
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQueryEcmtShortTerm')]
    public function testHandleQueryEcmtShortTerm(mixed $queryParams, mixed $expectedRepoMethod, mixed $expectedRepoParams): void
    {
        $ips1Id = 20;
        $ips2Id = 40;
        $ips3Id = 60;

        $ips1Year = 2021;
        $ips2Year = 2022;

        $ips1 = m::mock(IrhpPermitStock::class);
        $ips1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($ips1Id);
        $ips1->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn('period.name.key.1');
        $ips1->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($ips1Year);

        $ips2 = m::mock(IrhpPermitStock::class);
        $ips2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($ips2Id);
        $ips2->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn('period.name.key.2');
        $ips2->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($ips2Year);

        $ips3 = m::mock(IrhpPermitStock::class);
        $ips3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($ips3Id);

        $ipw1 = m::mock(IrhpPermitWindow::class);
        $ipw1->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($ips1);

        $ipw2 = m::mock(IrhpPermitWindow::class);
        $ipw2->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($ips2);

        $ipw3 = m::mock(IrhpPermitWindow::class);
        $ipw3->shouldReceive('getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($ips3);

        $irhpPermitWindows = [
            $ipw3,
            $ipw2,
            $ipw1,
        ];

        $query = AvailableStocksQuery::create($queryParams);

        $this->repoMap['IrhpPermitWindow']->shouldReceive($expectedRepoMethod)
            ->withArgs($expectedRepoParams)
            ->andReturn($irhpPermitWindows);

        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->with($ips1Id)
            ->once()
            ->andReturn(true);
        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->with($ips2Id)
            ->once()
            ->andReturn(true);
        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->with($ips3Id)
            ->once()
            ->andReturn(false);

        $this->assertSame(
            [
                'stocks' => [
                    $ips1Id => [
                        'id' => $ips1Id,
                        'periodNameKey' => 'period.name.key.1',
                        'year' => $ips1Year
                    ],
                    $ips2Id => [
                        'id' => $ips2Id,
                        'periodNameKey' => 'period.name.key.2',
                        'year' => $ips2Year
                    ],
                ],
                'hasStocks' => true,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public static function dpTestHandleQueryEcmtShortTerm(): array
    {
        $irhpPermitType = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM;
        $year = 2020;

        return [
            'with year' => [
                [
                    'irhpPermitType' => $irhpPermitType,
                    'year' => $year,
                ],
                'fetchOpenWindowsByTypeYear',
                [
                    $irhpPermitType,
                    m::type(DateTime::class),
                    $year,
                    false
                ]
            ],
            'without year' => [
                [
                    'irhpPermitType' => $irhpPermitType,
                ],
                'fetchOpenWindowsByType',
                [
                    $irhpPermitType,
                    m::type(DateTime::class),
                    false
                ]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQueryEcmtAnnual')]
    public function testHandleQueryEcmtAnnual(mixed $queryParams, mixed $expectedRepoMethod, mixed $expectedRepoParams): void
    {
        $ips1Id = 20;
        $ips1Year = 2020;

        $ips1 = m::mock(IrhpPermitStock::class);
        $ips1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($ips1Id);
        $ips1->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn('');
        $ips1->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($ips1Year);

        $ipw1 = m::mock(IrhpPermitWindow::class);
        $ipw1->shouldReceive('getIrhpPermitStock')
            ->andReturn($ips1);

        $irhpPermitWindows = [
            $ipw1,
        ];

        $query = AvailableStocksQuery::create($queryParams);

        $this->repoMap['IrhpPermitWindow']->shouldReceive($expectedRepoMethod)
            ->withArgs($expectedRepoParams)
            ->andReturn($irhpPermitWindows);

        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->never();

        $this->assertSame(
            [
                'stocks' => [
                    $ips1Id => [
                        'id' => $ips1Id,
                        'periodNameKey' => '',
                        'year' => $ips1Year
                    ],
                ],
                'hasStocks' => true,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public static function dpTestHandleQueryEcmtAnnual(): array
    {
        $irhpPermitType = IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT;
        $year = 2020;

        return [
            'with year' => [
                [
                    'irhpPermitType' => $irhpPermitType,
                    'year' => $year,
                ],
                'fetchOpenWindowsByTypeYear',
                [
                    $irhpPermitType,
                    m::type(DateTime::class),
                    $year,
                    false
                ]
            ],
            'without year' => [
                [
                    'irhpPermitType' => $irhpPermitType,
                ],
                'fetchOpenWindowsByType',
                [
                    $irhpPermitType,
                    m::type(DateTime::class),
                    false
                ]
            ]
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQueryUnsupportedType')]
    public function testHandleQueryUnsupportedType(mixed $irhpPermitType): void
    {
        $query = AvailableStocksQuery::create(
            [
                'irhpPermitType' => $irhpPermitType,
                'year' => 2020,
            ]
        );

        $this->assertEquals(
            [
                'stocks' => [],
                'hasStocks' => false,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public static function dpTestHandleQueryUnsupportedType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQueryNoStocks')]
    public function testHandleQueryNoStocks(mixed $irhpPermitType): void
    {
        $query = AvailableStocksQuery::create(
            [
                'irhpPermitType' => $irhpPermitType,
                'year' => 2020,
            ]
        );

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByTypeYear')
            ->with(
                $query->getIrhpPermitType(),
                m::type(DateTime::class),
                $query->getYear(),
                false
            )
            ->once()
            ->andReturn([]);

        $this->assertEquals(
            [
                'stocks' => [],
                'hasStocks' => false,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public static function dpHandleQueryNoStocks(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
        ];
    }
}
