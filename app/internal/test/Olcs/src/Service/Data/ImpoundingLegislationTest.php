<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\ImpoundingLegislation;
use Mockery as m;
use CommonTest\Service\Data\AbstractDataServiceTestCase;

/**
 * Class ImpoundingLegislationTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ImpoundingLegislationTest extends AbstractDataServiceTestCase
{
    /**
     * Tests fetchListOptions when no licence is present
     */
    public function testFetchListOptionsNoLicence()
    {
        $mockLicenceService = m::mock('\Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')
            ->andReturn([])
            ->once()
            ->getMock();

        $sut = new ImpoundingLegislation();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('impound_legislation_goods_gb', $this->getSingleSource());

        $this->assertEquals($this->getSingleExpected(), $sut->fetchListOptions([]));
    }

    /**
     * @dataProvider provideFetchListOptions
     *
     * @param $niFlag
     * @param $goodsOrPsv
     * @param $expectedList
     */
    public function testFetchListOptions($niFlag, $goodsOrPsv, $expectedList)
    {
        $mockLicenceService = m::mock('\Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag'=> $niFlag,
                    'goodsOrPsv' => ['id'=> $goodsOrPsv],
                    'trafficArea' => ['id'=> 'B']
                ]
            )
            ->once()
            ->getMock();

        $sut = new ImpoundingLegislation();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData($expectedList, $this->getSingleSource());

        $this->assertEquals($this->getSingleExpected(), $sut->fetchListOptions([]));
    }

    /**
     * Tests fetchListOptions when no data is returned
     */
    public function testFetchListOptionsNoData()
    {
        $mockLicenceService = m::mock('\Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag'=> 'Y',
                    'goodsOrPsv' => ['id'=> 'lcat_gv'],
                    'trafficArea' => ['id'=> 'B']
                ]
            )
            ->once()
            ->getMock();

        $sut = new ImpoundingLegislation();
        $sut->setLicenceService($mockLicenceService);
        $sut->setData('impound_legislation_goods_ni', '');

        $this->assertEquals([], $sut->fetchListOptions([]));
    }

    /**
     * Data provider for testFetchListOptions
     *
     * @return array
     */
    public function provideFetchListOptions()
    {
        return [
            ['Y', 'lcat_psv', 'impound_legislation_psv_gb'],
            ['Y', 'lcat_gv', 'impound_legislation_goods_ni'],
            ['N', 'lcat_gv', 'impound_legislation_goods_gb']
        ];
    }

    /**
     * @return array
     */
    protected function getSingleExpected()
    {
        $expected = [
            'val-1' => 'Value 1',
            'val-2' => 'Value 2',
            'val-3' => 'Value 3',
        ];

        return $expected;
    }

    /**
     * @return array
     */
    protected function getSingleSource()
    {
        $source = [
            0 => ['id' => 'val-1', 'description' => 'Value 1'],
            1 => ['id' => 'val-2', 'description' => 'Value 2'],
            2 => ['id' => 'val-3', 'description' => 'Value 3']
        ];

        return $source;
    }
}
