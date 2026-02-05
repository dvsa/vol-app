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
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter($expected, $value)
    {
        $sut = new Via();

        $result = $sut->filter(['via' => $value]);
        $this->assertEquals($expected, $result['via']);
    }

    public static function provideFilter()
    {
        return [
            ['via1, via2', ['via1', 'via2']],
            ['via1', ['via1']],
            ['via1', 'via1'],
            [null, null]
        ];
    }
}
