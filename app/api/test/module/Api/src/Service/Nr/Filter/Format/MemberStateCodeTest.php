<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode::class)]
final class MemberStateCodeTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFilter')]
    public function testFilter(mixed $value, mixed $expect): void
    {
        $value = [
            'memberStateCode' => $value,
        ];

        $this->assertEquals([
            'memberStateCode' => $expect,
        ], new MemberStateCode()->filter($value));
    }

    public static function dpTestFilter(): \Iterator
    {
        yield [
            'value' => 'gb',
            'expect' => 'gb',
        ];
        yield ['uk', 'GB'];
        yield ['UK', 'GB'];
    }
}
