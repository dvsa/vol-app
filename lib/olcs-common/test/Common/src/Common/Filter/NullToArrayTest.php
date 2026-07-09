<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\NullToArray;

/**
 * Class NullToArrayTest
 * @package CommonTest\Filter
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Filter\NullToArray::class)]
final class NullToArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     * @param $input    value to be passed into filter
     * @param $expected expected value to be returned from filter
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('getValueDataProvider')]
    public function testFilter($input, $expected): void
    {
        $filter = new NullToArray();
        $this->assertEquals($expected, $filter->filter($input));
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | int | string | false | null)>>
     *
     * @psalm-return array{'Bool value should return bool': list{false, false}, 'Null value should return empty array': list{null, array<never, never>}, 'Integer value should return same number': list{1, 1}, 'String should return a string': list{'string', 'string'}, 'Array should return a array': list{array{a: 'b'}, array{a: 'b'}}}
     */
    public static function getValueDataProvider(): \Iterator
    {
        yield 'Bool value should return bool' => [false, false];
        yield 'Null value should return empty array' => [null, []];
        yield 'Integer value should return same number' => [1, 1];
        yield 'String should return a string' => ['string', 'string'];
        yield 'Array should return a array' => [
            ['a' => 'b'],
            ['a' => 'b'],
        ];
    }
}
