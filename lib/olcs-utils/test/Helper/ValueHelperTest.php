<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Helper;

use Dvsa\Olcs\Utils\Helper\ValueHelper;

/**
 * Value Helper Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ValueHelperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('isOnProvider')]
    public function testIsOn($value, $expected)
    {
        $this->assertEquals($expected, ValueHelper::isOn($value));
    }

    public static function isOnProvider(): \Iterator
    {
        yield [
            'Y',
            true
        ];
        yield [
            true,
            true
        ];
        yield [
            1,
            true
        ];
        yield [
            '1',
            true
        ];
        yield [
            'N',
            false
        ];
        yield [
            false,
            false
        ];
        yield [
            0,
            false
        ];
        yield [
            '0',
            false
        ];
        yield [
            '2',
            false
        ];
        yield [
            'A',
            false
        ];
    }
}
