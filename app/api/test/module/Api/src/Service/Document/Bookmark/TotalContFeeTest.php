<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query as DomainQry;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Service\Document\Bookmark\TotalContFee;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\TotalContFee
 */
final class TotalContFeeTest extends MockeryTestCase
{
    /** @var  TotalContFee */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new TotalContFee();
    }

    public function testGetQuery(): void
    {
        /** @var \Dvsa\Olcs\Api\Service\Date $mockDateHelper */
        $mockDateHelper = m::mock(\Dvsa\Olcs\Api\Service\Date::class)
            ->shouldReceive('getDate')
            ->with('Y-m-d')
            ->andReturn('2015-05-01')
            ->once()
            ->getMock();
        $this->sut->setDateHelper($mockDateHelper);

        $data = [
            'goodsOrPsv' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            'niFlag' => 'Y',
            'trafficAreaId' => 'X',
        ];
        /** @var DomainQry\Bookmark\TotalContFee $query */
        $query = $this->sut->getQuery($data);

        $this->assertInstanceOf(DomainQry\Bookmark\TotalContFee::class, $query);
        $this->assertEquals(TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE, $query->getTrafficArea());
        $this->assertEquals(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL, $query->getLicenceType());
        $this->assertEquals(Licence::LICENCE_CATEGORY_GOODS_VEHICLE, $query->getGoodsOrPsv());
        $this->assertEquals('2015-05-01', $query->getEffectiveFrom());
    }

    public function testRenderWithNoTotalContFee(): void
    {
        $this->sut->setData(null);
        $this->assertEquals('', $this->sut->render());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('resultsProvider')]
    public function testRenderWithTotalContFee(mixed $results): void
    {
        $this->sut->setData($results);
        $this->assertEquals('123,456', $this->sut->render());
    }

    public static function resultsProvider(): \Iterator
    {
        yield [
            [
                'fixedValue' => '123456',
                'effectiveFrom' => '2015-01-01'
            ],
        ];
        yield [
            [
                'fixedValue' => '0',
                'fiveYearValue' => '123456',
                'effectiveFrom' => '2015-01-01'
            ],
        ];
    }
}
