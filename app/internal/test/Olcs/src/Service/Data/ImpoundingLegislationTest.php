<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\Licence as LicenceDataService;
use CommonTest\Common\Service\Data\RefDataTestCase;
use Olcs\Service\Data\ImpoundingLegislation;
use Mockery as m;

/**
 * Class ImpoundingLegislationTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ImpoundingLegislationTest extends RefDataTestCase
{
    /** @var ImpoundingLegislation */
    private $sut;

    /** @var LicenceDataService */
    protected $licenceDataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->licenceDataService = m::mock(LicenceDataService::class);

        $this->sut = new ImpoundingLegislation(
            $this->refDataServices,
            $this->licenceDataService
        );
    }

    /**
     * Tests fetchListOptions when no licence is present
     */
    public function testFetchListOptionsNoLicence()
    {
        $this->licenceDataService->shouldReceive('fetchLicenceData')
            ->once()
            ->andReturn([]);

        $this->sut->setData('impound_legislation_goods_gb', $this->getSingleSource());

        $this->assertEquals($this->getSingleExpected(), $this->sut->fetchListOptions([]));
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
        $this->licenceDataService->shouldReceive('fetchLicenceData')
            ->once()
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag' => $niFlag,
                    'goodsOrPsv' => ['id' => $goodsOrPsv],
                    'trafficArea' => ['id' => 'B']
                ]
            );

        $this->sut->setData($expectedList, $this->getSingleSource());

        $this->assertEquals($this->getSingleExpected(), $this->sut->fetchListOptions([]));
    }

    /**
     * Tests fetchListOptions when no data is returned
     */
    public function testFetchListOptionsNoData()
    {
        $this->licenceDataService->shouldReceive('fetchLicenceData')
            ->once()
            ->andReturn(
                [
                    'id' => 7,
                    'niFlag' => 'Y',
                    'goodsOrPsv' => ['id' => 'lcat_gv'],
                    'trafficArea' => ['id' => 'B']
                ]
            );

        $this->sut->setData('impound_legislation_goods_ni', '');

        $this->assertEquals([], $this->sut->fetchListOptions([]));
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
