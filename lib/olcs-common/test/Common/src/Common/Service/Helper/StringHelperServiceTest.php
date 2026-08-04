<?php

/**
 * String Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Helper;

use Common\Service\Helper\StringHelperService;

/**
 * String Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class StringHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\StringHelperService
     */
    private $sut;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new StringHelperService();
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('string_helper_service')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testDashToCamel($dash, $camel): void
    {
        $this->assertEquals($camel, $this->sut->dashToCamel($dash));
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('string_helper_service')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testCamelToDash($dash, $camel): void
    {
        $this->assertEquals($dash, $this->sut->camelToDash($camel));
    }

    /**
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return list{list{'this-that', 'ThisThat'}, list{'foo-bar-baz', 'FooBarBaz'}, list{'foo', 'Foo'}, list{'foo cake this-that', 'Foo cake thisThat'}}
     */
    public static function provider(): \Iterator
    {
        yield [
            'this-that',
            'ThisThat'
        ];
        yield [
            'foo-bar-baz',
            'FooBarBaz'
        ];
        yield [
            'foo',
            'Foo'
        ];
        yield [
            'foo cake this-that',
            'Foo cake thisThat'
        ];
    }
}
