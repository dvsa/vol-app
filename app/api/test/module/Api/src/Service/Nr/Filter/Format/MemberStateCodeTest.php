<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode::class)]
class MemberStateCodeTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFilter')]
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

    public static function dpTestFilter()
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
