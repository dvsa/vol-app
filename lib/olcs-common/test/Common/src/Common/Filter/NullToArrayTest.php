<?php

namespace CommonTest\Filter;

use Common\Filter\NullToArray;

/**
 * Class NullToArrayTest
 * @package CommonTest\Filter
 * @covers \Common\Filter\NullToArray
 */
class NullToArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValueDataProvider
     *
     * @param $input    value to be passed into filter
     * @param $expected expected value to be returned from filter
     */
    public function testFilter($input, $expected): void
    {
        $filter = new NullToArray();
        $this->assertEquals($expected, $filter->filter($input));
    }

    /**
     * @return (false|int|null|string|string[])[][]
     *
     * @psalm-return array{'Bool value should return bool': list{false, false}, 'Null value should return empty array': list{null, array<never, never>}, 'Integer value should return same number': list{1, 1}, 'String should return a string': list{'string', 'string'}, 'Array should return a array': list{array{a: 'b'}, array{a: 'b'}}}
     */
    public function getValueDataProvider(): array
    {
        return [
            'Bool value should return bool'           => [false, false],
            'Null value should return empty array'    => [null, []],
            'Integer value should return same number' => [1, 1],
            'String should return a string'           => ['string', 'string'],
            'Array should return a array'             => [
                ['a' => 'b'],
                ['a' => 'b'],
            ],
        ];
    }
}
