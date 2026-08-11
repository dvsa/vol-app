<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class NoticePeriodTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter
 */
final class NoticePeriodTest extends TestCase
{
    /**
     * @param $data
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter(mixed $data, mixed $expected): void
    {
        $sut = new NoticePeriod();
        $result = $sut->filter($data);

        $this->assertEquals($expected, $result['busNoticePeriod']);
    }

    public static function provideFilter(): \Iterator
    {
        yield [['trafficAreas' => ['English']], 2];
        yield [['trafficAreas' => ['Scottish']], 1];
        yield [['trafficAreas' => ['Welsh']], 3];
        yield [['trafficAreas' => ['English', 'Welsh']], 3];
        yield [['trafficAreas' => ['Welsh', 'English']], 3];
        yield [['trafficAreas' => ['English', 'Scottish']], 1];
        yield [['trafficAreas' => ['Scottish', 'English']], 1];
        yield [['trafficAreas' => ['Welsh', 'Scottish']], 1];
        yield [['trafficAreas' => ['Scottish', 'Welsh']], 1];
    }
}
