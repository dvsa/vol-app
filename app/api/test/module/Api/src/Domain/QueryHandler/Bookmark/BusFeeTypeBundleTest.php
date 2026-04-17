<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\BusFeeTypeBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusFeeTypeBundle as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;

/**
 * BusFeeTypeBundle Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeeTypeBundleTest extends QueryHandlerTestCase
{
    protected $refData = [
        FeeTypeEntity::FEE_TYPE_BUSVAR,
        FeeTypeEntity::FEE_TYPE_BUSAPP,
        LicenceEntity::LICENCE_CATEGORY_PSV,
        LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL,
    ];

    public function setUp(): void
    {
        $this->sut = new BusFeeTypeBundle();
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    /**
     *
     * @param int         $variationNumber
     * @param string      $feeType
     * @param bool        $isScotland
     * @param string|null $trafficAreaCode
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleQueryProvider')]
    public function testHandleQuery(mixed $variationNumber, mixed $feeType, mixed $isScotland, mixed $trafficAreaCode): void
    {
        $receivedDate = '2017-12-25';
        $receivedDateTime = new \DateTime('2017-12-25');
        $feeTypeRef = $this->refData[$feeType];
        $goodsOrPsv = $this->refData[LicenceEntity::LICENCE_CATEGORY_PSV];
        $licenceType = $this->refData[LicenceEntity::LICENCE_TYPE_STANDARD_NATIONAL];
        $result = ['serialized'];

        $query = Qry::create(['id' => 111]);

        /** @var m\mockInterface $ta */
        $ta = m::mock(TrafficAreaEntity::class);
        $ta->shouldReceive('getIsScotland')->once()->andReturn($isScotland);

        /** @var m\mockInterface $busReg */
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getLicence->getLicenceType')->once()->andReturn($licenceType);
        $busReg->shouldReceive('getLicence->getGoodsOrPsv')->once()->andReturn($goodsOrPsv);
        $busReg->shouldReceive('getLicence->getTrafficArea')->once()->andReturn($ta);
        $busReg->shouldReceive('getReceivedDate')->once()->andReturn($receivedDate);
        $busReg->shouldReceive('processDate')->with($receivedDate)->once()->andReturn($receivedDateTime);
        $busReg->shouldReceive('getVariationNo')->once()->andReturn($variationNumber);

        /** @var m\mockInterface $feeTypeEntity */
        $feeTypeEntity = m::mock(FeeTypeEntity::class);
        $feeTypeEntity->shouldReceive('serialize')->once()->andReturn($result);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $feeTypeRef,
                $goodsOrPsv,
                $licenceType,
                $receivedDateTime,
                $trafficAreaCode
            )
            ->once()
            ->andReturn($feeTypeEntity);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($busReg);

        $this->assertEquals($result, $this->sut->handleQuery($query));
    }

    /**
     * Provider for testHandleQuery
     *
     * @return array
     */
    public static function handleQueryProvider(): array
    {
        return [
            [0, FeeTypeEntity::FEE_TYPE_BUSAPP, true, TrafficAreaEntity::SCOTTISH_TRAFFIC_AREA_CODE],
            [1, FeeTypeEntity::FEE_TYPE_BUSVAR, true, TrafficAreaEntity::SCOTTISH_TRAFFIC_AREA_CODE],
            [0, FeeTypeEntity::FEE_TYPE_BUSAPP, false, null],
            [1, FeeTypeEntity::FEE_TYPE_BUSVAR, false, null],
        ];
    }
}
