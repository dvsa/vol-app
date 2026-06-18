<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\ReturnToAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\ReturnToAddress
 */
class ReturnToAddressTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestInvoke
     */
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

        static::assertEquals($expect, $actual);
        static::assertEquals($actual, $actualStatic);
    }

    /**
     * @return (bool|null|string)[][]
     *
     * @psalm-return list{array{isNi: false, separator: null, expect: 'Office of the Traffic Commissioner, Quarry House, Leeds, LS2 7UE'}, array{isNi: false, separator: '<br />', expect: 'Office of the Traffic Commissioner<br />Quarry House<br />Leeds<br />LS2 7UE'}, array{isNi: true, separator: null, expect: 'Department for Infrastructure, Quarry House, Leeds, LS2 7UE'}, array{isNi: true, separator: '<br />', expect: 'Department for Infrastructure<br />Quarry House<br />Leeds<br />LS2 7UE'}}
     */
    public function dpTestInvoke(): array
    {
        return [
            [
                'isNi' => false,
                'separator' => null,
                'expect' => 'Office of the Traffic Commissioner, Quarry House, Leeds, LS2 7UE',
            ],
            [
                'isNi' => false,
                'separator' => '<br />',
                'expect' => 'Office of the Traffic Commissioner<br />Quarry House<br />Leeds<br />LS2 7UE',
            ],
            [
                'isNi' => true,
                'separator' => null,
                'expect' => 'Department for Infrastructure, Quarry House, Leeds, LS2 7UE',
            ],
            [
                'isNi' => true,
                'separator' => '<br />',
                'expect' => 'Department for Infrastructure<br />Quarry House<br />Leeds<br />LS2 7UE',
            ],
        ];
    }
}
