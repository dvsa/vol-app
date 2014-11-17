<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\PiVenue;
use Olcs\Service\Data\Licence;
use Mockery as m;

/**
 * Class PiVenue Test
 * @package CommonTest\Service
 */
class PiVenueTest extends \PHPUnit_Framework_TestCase
{
    public function testGetServiceName()
    {
        $sut = new PiVenue();
        $this->assertEquals('PiVenue', $sut->getServiceName());
    }

    public function testFormatData()
    {
        $source = $this->getSingleSource();
        $expected = $this->getSingleExpected();

        $sut = new PiVenue();

        $this->assertEquals($expected, $sut->formatData($source));
    }

    /**
     * @dataProvider provideFetchListOptions
     * @param $input
     * @param $expected
     */
    public function testFetchListOptions($input, $expected)
    {
        $mockLicenceService = $this->getMock('\Common\Service\Data\Licence');
        $mockLicenceService->expects($this->once())
            ->method('fetchLicenceData')
            ->willReturn(['niFlag'=> true, 'goodsOrPsv' => ['id'=>'lcat_gv'], 'trafficArea' => ['id' => 'B']]);

        $sut = new PiVenue();
        $sut->setLicenceService($mockLicenceService);

        $sut->setData('PiVenue', $input);

        $this->assertEquals($expected, $sut->fetchListOptions(''));
    }

    public function provideFetchListOptions()
    {
        return [
            [$this->getSingleSource(), $this->getSingleExpected()],
            [false, []]
        ];
    }

    /**
     * @dataProvider provideFetchListData
     * @param $data
     * @param $expected
     */
    public function testFetchListData($data, $expected)
    {
        $params = ['trafficArea' => 'B'];

        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('', $params)->andReturn($data);

        $sut = new PiVenue();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($expected, $sut->fetchListData($params));
        $sut->fetchListData($params); //ensure data is cached
    }

    public function provideFetchListData()
    {
        return [
            [false, false],
            [['Results' => $this->getSingleSource()], $this->getSingleSource()],
            [['some' => 'data'],  false]
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
            ['id' => 'val-1', 'name' => 'Value 1'],
            ['id' => 'val-2', 'name' => 'Value 2'],
            ['id' => 'val-3', 'name' => 'Value 3'],
        ];
        return $source;
    }
}
