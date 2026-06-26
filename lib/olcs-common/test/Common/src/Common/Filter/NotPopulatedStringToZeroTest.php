<?php

namespace CommonTest\Filter;

use Common\Filter\NotPopulatedStringToZero;

/**
 * Class NotPopulatedStringToZeroTest
 * @package CommonTest\Filter
 */
class NotPopulatedStringToZeroTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $input
     * @param $output
     */
    public function testFilter($input, $output): void
    {
        $sut = new NotPopulatedStringToZero();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            [new \stdClass(), '0'],
            [4, '0'],
            [null, '0'],
            ['', '0'],
            ['0', '0'],
            ['1', '1'],
            ['2', '2'],
            ['15', '15'],
        ];
    }
}
