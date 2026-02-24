<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Lva;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Lva\AbstractGoodsVehiclesExport;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Lva\AbstractGoodsVehiclesExport::class)]
class AbstractGoodsVehiclesExportTest extends QueryHandlerTestCase
{
    /** @var  DummyAbstractGoodsVehiclesExport */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new class extends AbstractGoodsVehiclesExport {
            public function getData(QueryBuilder $qb): array
            {
                return parent::getData($qb);
            }

            public function handleQuery(QueryInterface $query): void
            {
            }
        };

        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    public function testGetData(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $dbRow1 = [
            'vrm' => 'unit_Vrm_1',
            'platedWeight' => 'unit_platedWeight_1',
            'specifiedDate' => 'unit_specifiedDate_1',
            'removalDate' => 'unit_removalDate_1',
            'discId' => null,
        ];
        $dbRow2 = [
            'vrm' => 'unit_Vrm_2',
            'platedWeight' => 'unit_platedWeight_2',
            'specifiedDate' => 'unit_specifiedDate_2',
            'removalDate' => 'unit_removalDate_2',
            'discId' => 999,
            'discNo' => 'unit_DiscNo',
            'ceasedDate' => 'unit_CeasedDate',
        ];

        $mockDbIterator = m::mock(\Doctrine\ORM\Internal\Hydration\IterableResult::class)
            ->shouldReceive('next')->once()->andReturn([$dbRow1])
            ->shouldReceive('next')->once()->andReturn([$dbRow2])
            ->shouldReceive('next')->once()->andReturn(false)
            ->getMock();

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForExport')->once()->with($qb)->andReturn($mockDbIterator);

        //  call & check
        $actual = $this->sut->getData($qb);

        static::assertEquals(
            [
                'results' => [
                    [
                        'vehicle' => [
                            'vrm' => 'unit_Vrm_1',
                            'platedWeight' => 'unit_platedWeight_1',
                        ],
                        'specifiedDate' => 'unit_specifiedDate_1',
                        'removalDate' => 'unit_removalDate_1',
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'unit_Vrm_2',
                            'platedWeight' => 'unit_platedWeight_2',
                        ],
                        'specifiedDate' => 'unit_specifiedDate_2',
                        'removalDate' => 'unit_removalDate_2',
                        'goodsDiscs' => [
                            [
                                'discNo' => 'unit_DiscNo',
                                'ceasedDate' => 'unit_CeasedDate',
                            ],
                        ],
                    ],
                ],
                'count' => 2,
            ],
            $actual
        );
    }
}
