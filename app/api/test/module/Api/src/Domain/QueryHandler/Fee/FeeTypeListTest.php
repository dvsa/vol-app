<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\FeeTypeList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Query\Fee\FeeTypeList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * FeeType List Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeTypeListTest extends QueryHandlerTestCase
{
    public const IRFO_FEE_REF_ID = 'IRFO0001';

    public const FEE_REF_1_ID = 'APP';
    public const FEE_REF_2_ID = 'GRANT';

    public const FEE_DESC = 'fee type ';

    public const ORG_ID = 9999;

    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('FeeType', Repository\FeeType::class);
        $this->mockRepo('IrfoGvPermit', Repository\IrfoGvPermit::class);
        $this->mockRepo('IrfoPsvAuth', Repository\IrfoPsvAuth::class);

        parent::setUp();
    }

    public function testTrafficAreaApplication(): void
    {
        $query = Qry::create([]);

        $mockTrafficArea = m::mock(TrafficArea::class);

        $this->repoMap['FeeType']
            ->shouldReceive('getReference')
            ->with(Licence::class, null)
            ->andReturn(null)
            //
            ->shouldReceive('getReference')
            ->with(Application::class, null)
            ->andReturn(
                m::mock()->shouldReceive('getLicence')
                    ->andReturn(
                        m::mock()->shouldReceive('getTrafficArea')
                            ->andReturn($mockTrafficArea)
                            ->getMock()
                    )
                    ->getMock()
            )
            //
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn(new \ArrayObject());

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [],
            'count' => 0,
            'valueOptions' => [
                'irfoGvPermit' => [],
                'irfoPsvAuth' => [],
            ],
            'showQuantity' => false,
            'showVatRate' => false
        ];

        static::assertEquals($expected, $result);
    }

    public function testIrfo(): void
    {
        $query = Qry::create(['organisation' => self::ORG_ID, 'currentFeeType' => 123]);

        $mockOrganisation = m::mock(Organisation::class);

        $this->repoMap['FeeType']
            ->shouldReceive('getReference')
            ->with(Licence::class, null)
            ->andReturn(null)
            //
            ->shouldReceive('getReference')
            ->with(Application::class, null)
            ->andReturn(null)
            //
            ->shouldReceive('getReference')
            ->twice()
            ->with(Organisation::class, self::ORG_ID)
            ->andReturn($mockOrganisation)
            //
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn(new \ArrayObject())
            //
            ->shouldReceive('fetchById')
            ->with(123)
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('isShowQuantity')
                ->andReturn(true)
                ->once()
                ->shouldReceive('getVatRate')
                ->andReturn(20)
                ->once()
                ->getMock()
            );

        //  irfo Gv Permit
        $mockPermit = m::mock(IrfoGvPermit::class);
        $mockPermit->shouldReceive('getId')
            ->twice()
            ->andReturn(69);
        $mockPermit->shouldReceive('getIrfoGvPermitType->getDescription')
            ->once()
            ->andReturn('permit description');

        $this->repoMap['IrfoGvPermit']
            ->shouldReceive('fetchByOrganisation')
            ->once()
            ->with($mockOrganisation)
            ->andReturn([$mockPermit]);

        //  irfo Psv Auth
        $mockAuth = m::mock(IrfoPsvAuth::class);
        $mockAuth->shouldReceive('getId')
            ->twice()
            ->andReturn(69);
        $mockAuth->shouldReceive('getIrfoPsvAuthType->getDescription')
            ->once()
            ->andReturn('auth description');

        $this->repoMap['IrfoPsvAuth']
            ->shouldReceive('fetchByOrganisation')
            ->once()
            ->with($mockOrganisation)
            ->andReturn([$mockAuth]);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [],
            'count' => 0,
            'valueOptions' => [
                'irfoGvPermit' => [
                    69 => '69 (permit description)',
                ],
                'irfoPsvAuth' => [
                    69 => '69 (auth description)',
                ],
            ],
            'showQuantity' => true,
            'showVatRate' => true
        ];

        static::assertEquals($expected, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestFilter')]
    public function testFilter(array $fees, mixed $mockTrafficArea, mixed $expect): void
    {
        $query = Qry::create([]);

        $mockList = new \ArrayObject();
        foreach ($fees as $fee) {
            $mockList->append(
                $this->getMockFeeType(
                    $fee['id'],
                    $fee['effectiveFrom'],
                    $fee['trafficArea'],
                    $fee['irfoFeeTypeRefId'],
                    $fee['feeTypeRefId']
                )
            );
        }

        $repo = $this->repoMap['FeeType'];
        $repo->shouldReceive('getReference')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getTrafficArea')
                    ->andReturn($mockTrafficArea)
                    ->getMock()
            );
        $repo->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockList);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => $expect['result'],
            'count' => count($expect['result']),
            'valueOptions' => [
                'feeType' => $expect['feeType'],
                'irfoGvPermit' => [],
                'irfoPsvAuth' => [],
            ],
            'showQuantity' => false,
            'showVatRate'  => false
        ];

        static::assertEquals($expected, $result);
    }

    public static function dataProviderTestFilter(): array
    {
        $mockTrafficAreaA = m::mock(TrafficArea::class);

        return [
            //  test effective data and few diff Fee ref types
            [
                'fees' => [
                    [
                        'id' => 24,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                    [
                        'id' => 25,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2014-01-01',
                    ],
                    [
                        'id' => 34,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_2_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                ],
                'mockTrafficArea' => null,
                'expect' => [
                    'result' => [
                        ['id' => 24],
                        ['id' => 34],
                    ],
                    'feeType' => [
                        24 => self::FEE_DESC . '24',
                        34 => self::FEE_DESC . '34',
                    ],
                ],
            ],
            //  test take only for specified traffic area
            [
                'fees' => [
                    [
                        'id' => 24,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                    [
                        'id' => 25,
                        'trafficArea' => $mockTrafficAreaA,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                    [
                        'id' => 34,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => null,
                        'feeTypeRefId' => self::FEE_REF_2_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                ],
                'mockTrafficArea' => $mockTrafficAreaA,
                'expect' => [
                    'result' => [
                        ['id' => 25],
                        ['id' => 34],
                    ],
                    'feeType' => [
                        25 => self::FEE_DESC . '25',
                        34 => self::FEE_DESC . '34',
                    ],
                ],
            ],
            //  test group by IfroFeeTypeRefId and FeeTypeRefId
            [
                'fees' => [
                    [
                        'id' => 24,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => self::IRFO_FEE_REF_ID,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                    [
                        'id' => 25,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => self::IRFO_FEE_REF_ID,
                        'feeTypeRefId' => self::FEE_REF_1_ID,
                        'effectiveFrom' => '2016-01-01',
                    ],
                    [
                        'id' => 26,
                        'trafficArea' => null,
                        'irfoFeeTypeRefId' => self::IRFO_FEE_REF_ID,
                        'feeTypeRefId' => self::FEE_REF_2_ID,
                        'effectiveFrom' => '2015-01-01',
                    ],
                ],
                'mockTrafficArea' => $mockTrafficAreaA,
                'expect' => [
                    'result' => [
                        ['id' => 25],
                        ['id' => 26],
                    ],
                    'feeType' => [
                        25 => self::FEE_DESC . '25',
                        26 => self::FEE_DESC . '26',
                    ],
                ],
            ],
        ];
    }

    private function getMockFeeType(
        mixed $id,
        mixed $effectiveFrom,
        mixed $trafficArea = null,
        mixed $irfoFeeRefId = null,
        mixed $feeRefId = null
    ): m\MockInterface {
        $irfoFeeTypeRefData = null;
        if ($irfoFeeRefId) {
            $irfoFeeTypeRefData = m::mock(RefData::class)
                ->shouldReceive('getId')
                ->andReturn($irfoFeeRefId)
                ->getMock();
        }

        $feeTypeRefData = null;
        if ($feeRefId) {
            $feeTypeRefData = m::mock(RefData::class)
                ->shouldReceive('getId')
                ->andReturn($feeRefId)
                ->getMock();
        }

        return m::mock(FeeType::class)
            ->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($id)
            //
            ->shouldReceive('getDescription')
            ->withNoArgs()
            ->andReturn(self::FEE_DESC . $id)
            //
            ->shouldReceive('getEffectiveFrom')
            ->withNoArgs()
            ->andReturn($effectiveFrom)
            //
            ->shouldReceive('serialize')
            ->withAnyArgs()
            ->andReturn(['id' => $id])
            //
            ->shouldReceive('getTrafficArea')
            ->withNoArgs()
            ->andReturn($trafficArea)
            //
            ->shouldReceive('getIrfoFeeType')
            ->withNoArgs()
            ->andReturn($irfoFeeTypeRefData)
            //
            ->shouldReceive('getFeeType')
            ->withNoArgs()
            ->andReturn($feeTypeRefData)
            //
            ->shouldReceive('getVatRate')
            ->withNoArgs()
            ->andReturn(0)
            //
            ->getMock();
    }
}
