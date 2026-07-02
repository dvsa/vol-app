<?php

/**
 * Postcode filter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Transfer\Filter;

use Dvsa\Olcs\Transfer\Filter\Postcode;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Postcode filter test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PostcodeTest extends MockeryTestCase
{
    /**
     * @dataProvider provideFilter
     * @param $input
     * @param $output
     */
    public function testFilter($input, $output)
    {
        $sut = new Postcode();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            ['LS9 6NF', 'LS9 6NF'],
            ['ls96nf', 'LS9 6NF'],
            ['ls 96nf', 'LS9 6NF'],
            ['ls96nf  ', 'LS9 6NF'],
            ['L23SW', 'L2 3SW'],
            ['L23SW ', 'L2 3SW'],
            ['w1a4aa', 'W1A 4AA'],
            ['   ', ''],
        ];
    }
}
