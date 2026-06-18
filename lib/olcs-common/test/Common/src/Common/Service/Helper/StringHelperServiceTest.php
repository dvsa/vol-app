<?php

/**
 * String Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\StringHelperService;

/**
 * String Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StringHelperServiceTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @dataProvider provider
     * @group helper_service
     * @group string_helper_service
     */
    public function testDashToCamel($dash, $camel): void
    {
        $this->assertEquals($camel, $this->sut->dashToCamel($dash));
    }

    /**
     * @dataProvider provider
     * @group helper_service
     * @group string_helper_service
     */
    public function testCamelToDash($dash, $camel): void
    {
        $this->assertEquals($dash, $this->sut->camelToDash($camel));
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'this-that', 'ThisThat'}, list{'foo-bar-baz', 'FooBarBaz'}, list{'foo', 'Foo'}, list{'foo cake this-that', 'Foo cake thisThat'}}
     */
    public function provider(): array
    {
        return [
            [
                'this-that',
                'ThisThat'
            ],
            [
                'foo-bar-baz',
                'FooBarBaz'
            ],
            [
                'foo',
                'Foo'
            ],
            [
                'foo cake this-that',
                'Foo cake thisThat'
            ]
        ];
    }
}
