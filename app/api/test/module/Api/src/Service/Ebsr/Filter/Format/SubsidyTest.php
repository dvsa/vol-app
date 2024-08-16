<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class SubsidyTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 */
class SubsidyTest extends TestCase
{
    /**
     * @dataProvider provideFilter
     * @param $expected
     * @param $value
     */
    public function testFilter($expected, $value)
    {
        $sut = new Subsidy();

        $result = $sut->filter(['subsidised' => $value]);
        $this->assertEquals($expected, $result['subsidised']);
    }

    public function provideFilter()
    {
        return [
            ['bs_no', 'none'],
            ['bs_yes', 'full'],
            ['bs_in_part', 'partial'],
            ['bs_no', null]
        ];
    }
}
