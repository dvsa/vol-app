<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\NotPopulatedStringToZero;

/**
 * Class NotPopulatedStringToZeroTest
 * @package CommonTest\Filter
 */
final class NotPopulatedStringToZeroTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $input
     * @param $output
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $output): void
    {
        $sut = new NotPopulatedStringToZero();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield [new \stdClass(), '0'];
        yield [4, '0'];
        yield [null, '0'];
        yield ['', '0'];
        yield ['0', '0'];
        yield ['1', '1'];
        yield ['2', '2'];
        yield ['15', '15'];
    }
}
