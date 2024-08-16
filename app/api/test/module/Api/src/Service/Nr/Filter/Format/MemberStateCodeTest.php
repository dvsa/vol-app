<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode;

/**
 * @covers \Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode
 */
class MemberStateCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpTestFilter
     */
    public function testFilter($value, $expect)
    {
        $value = [
            'memberStateCode' => $value,
        ];

        static::assertEquals(
            [
                'memberStateCode' => $expect,
            ],
            (new MemberStateCode())->filter($value)
        );
    }

    public function dpTestFilter()
    {
        return [
            [
                'value' => 'gb',
                'expect' => 'gb',
            ],
            ['uk', 'GB'],
            ['UK', 'GB'],
        ];
    }
}
