<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class NoticePeriodTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter
 */
class NoticePeriodTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $data
     * @param $expected
     */
    public function testFilter($data, $expected)
    {
        $sut = new NoticePeriod();
        $result = $sut->filter($data);

        $this->assertEquals($expected, $result['busNoticePeriod']);
    }

    public function provideFilter()
    {
        return [
            [['trafficAreas' => ['English']], 2],
            [['trafficAreas' => ['Scottish']], 1],
            [['trafficAreas' => ['Welsh']], 3],
            [['trafficAreas' => ['English', 'Welsh']], 3],
            [['trafficAreas' => ['Welsh', 'English']], 3],
            [['trafficAreas' => ['English', 'Scottish']], 1],
            [['trafficAreas' => ['Scottish', 'English']], 1],
            [['trafficAreas' => ['Welsh', 'Scottish']], 1],
            [['trafficAreas' => ['Scottish', 'Welsh']], 1],
        ];
    }
}
