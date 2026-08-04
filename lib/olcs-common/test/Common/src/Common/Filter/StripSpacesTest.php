<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\StripSpaces;

final class StripSpacesTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $expected): void
    {
        $sut = new StripSpaces();
        $this->assertEquals($expected, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield [null, null];
        yield ['string', 'string'];
        yield [' string', 'string'];
        yield ['string ', 'string'];
        yield ['st ring ', 'string'];
        yield ['  st ri   n g   ', 'string'];
        yield [['  st ri   n g   ', ' s  tri ng2  '], ['string', 'string2']];
    }
}
