<?php

/**
 * VRM filter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Filter;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Filter\Vrm;

/**
 * VRM filter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VrmTest extends MockeryTestCase
{
    /**
     * @dataProvider provideFilter
     * @param $input
     * @param $output
     */
    public function testFilter($input, $output): void
    {
        $sut = new Vrm();
        $this->assertEquals($output, $sut->filter($input));
    }

    /**
     * @return array
     */
    public function provideFilter()
    {
        return [
            ['km04 aBC', 'KM04ABC'],
            ['A   b   C  ', 'ABC'],
            // special translations
            ['II', '11'],
            ['SO', 'S0']
        ];
    }
}
