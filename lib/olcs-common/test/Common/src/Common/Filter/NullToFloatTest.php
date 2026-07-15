<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\NullToFloat;

/**
 * Class NullToFloatTest
 * @package CommonTest\Filter
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Filter\NullToFloat::class)]
final class NullToFloatTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     * @param $input
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getValueDataProvider')]
    public function testFilter($input, $expected): void
    {
        $filter = new NullToFloat();
        $this->assertEquals($expected, $filter->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function getValueDataProvider(): \Iterator
    {
        yield 'Bool value should return int of 0' => [false, 0];
        yield 'Null value should return int of 0' => [null, 0];
        yield 'Integer value should return same number' => [1, 1];
        yield 'String should return a string' => ['string','string'];
    }
}
