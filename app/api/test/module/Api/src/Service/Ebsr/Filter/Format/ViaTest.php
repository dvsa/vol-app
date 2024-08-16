<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Via;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class ViaTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 */
class ViaTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $expected
     * @param $value
     */
    public function testFilter($expected, $value)
    {
        $sut = new Via();

        $result = $sut->filter(['via' => $value]);
        $this->assertEquals($expected, $result['via']);
    }

    public function provideFilter()
    {
        return [
            ['via1, via2', ['via1', 'via2']],
            ['via1', ['via1']],
            ['via1', 'via1'],
            [null, null]
        ];
    }
}
