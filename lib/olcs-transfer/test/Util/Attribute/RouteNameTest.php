<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\RouteName;

/**
 * RouteName test
 */
final class RouteNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param  string $value    value passed from annotation
     * @param  string $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testInstantiationValue(string $value, $expected)
    {
        $sut = new RouteName($value);
        $this->assertSame($expected, $sut->getRouteName());
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function valueProvider(): \Iterator
    {
        yield [
            'foo-route', 'foo-route',
        ];
        yield [
            'bar-route', 'bar-route',
        ];
    }
}

