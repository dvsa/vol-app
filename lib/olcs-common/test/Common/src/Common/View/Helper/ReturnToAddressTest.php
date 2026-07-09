<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\ReturnToAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\ReturnToAddress
 */
final class ReturnToAddressTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvoke')]
    public function testInvoke($isNi, $separator, $expect): void
    {
        $sut = new ReturnToAddress();

        if ($separator !== null) {
            $actual = $sut($isNi, $separator);
            $actualStatic = $sut::getAddress($isNi, $separator);
        } else {
            $actual = $sut($isNi);
            $actualStatic = $sut::getAddress($isNi);
        }

        $this->assertEquals($expect, $actual);
        $this->assertEquals($actual, $actualStatic);
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string | null)>>
     *
     * @psalm-return list{array{isNi: false, separator: null, expect: 'Office of the Traffic Commissioner, Quarry House, Leeds, LS2 7UE'}, array{isNi: false, separator: '<br />', expect: 'Office of the Traffic Commissioner<br />Quarry House<br />Leeds<br />LS2 7UE'}, array{isNi: true, separator: null, expect: 'Department for Infrastructure, Quarry House, Leeds, LS2 7UE'}, array{isNi: true, separator: '<br />', expect: 'Department for Infrastructure<br />Quarry House<br />Leeds<br />LS2 7UE'}}
     */
    public static function dpTestInvoke(): \Iterator
    {
        yield [
            'isNi' => false,
            'separator' => null,
            'expect' => 'Office of the Traffic Commissioner, Quarry House, Leeds, LS2 7UE',
        ];
        yield [
            'isNi' => false,
            'separator' => '<br />',
            'expect' => 'Office of the Traffic Commissioner<br />Quarry House<br />Leeds<br />LS2 7UE',
        ];
        yield [
            'isNi' => true,
            'separator' => null,
            'expect' => 'Department for Infrastructure, Quarry House, Leeds, LS2 7UE',
        ];
        yield [
            'isNi' => true,
            'separator' => '<br />',
            'expect' => 'Department for Infrastructure<br />Quarry House<br />Leeds<br />LS2 7UE',
        ];
    }
}
