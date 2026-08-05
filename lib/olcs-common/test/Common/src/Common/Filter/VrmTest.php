<?php

/**
 * VRM filter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Filter;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Filter\Vrm;

/**
 * VRM filter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class VrmTest extends MockeryTestCase
{
    /**
     * @param $input
     * @param $output
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $output): void
    {
        $sut = new Vrm();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield ['km04 aBC', 'KM04ABC'];
        yield ['A   b   C  ', 'ABC'];
        // special translations
        yield ['II', '11'];
        yield ['SO', 'S0'];
    }
}
