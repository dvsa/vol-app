<?php

declare(strict_types=1);

namespace CommonTest\Filter;

use Common\Filter\DateSelectNullifier;

/**
 * Class DateSelectNullifierTest
 * @package CommonTest\Filter
 */
final class DateSelectNullifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $input
     * @param $output
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $output): void
    {
        $sut = new DateSelectNullifier();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield [null, null];
        yield ['', null];
        yield ['string', 'string'];
        yield [['day' => '', 'year' => '', 'month' => ''], null];
        yield [['day' => '04', 'year' => '2012', 'month' => ''], null];
        yield [['day' => '04', 'year' => '2012', 'month' => '10'], '2012-10-04'];
    }
}
