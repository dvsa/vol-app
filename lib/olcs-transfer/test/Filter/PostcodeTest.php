<?php

/**
 * Postcode filter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Filter;

use Dvsa\Olcs\Transfer\Filter\Postcode;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Postcode filter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class PostcodeTest extends MockeryTestCase
{
    /**
     * @param $input
     * @param $output
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($input, $output)
    {
        $sut = new Postcode();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideFilter(): \Iterator
    {
        yield ['LS9 6NF', 'LS9 6NF'];
        yield ['ls96nf', 'LS9 6NF'];
        yield ['ls 96nf', 'LS9 6NF'];
        yield ['ls96nf  ', 'LS9 6NF'];
        yield ['L23SW', 'L2 3SW'];
        yield ['L23SW ', 'L2 3SW'];
        yield ['w1a4aa', 'W1A 4AA'];
        yield ['   ', ''];
    }
}
