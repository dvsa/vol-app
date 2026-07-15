<?php

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\Address;
use Mockery as m;

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class AddressTest extends \PHPUnit\Framework\TestCase
{
    public $sut;
    protected $dataHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataHelper = m::mock(DataHelperService::class);
        $this->sut = new Address($this->dataHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('AddressFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [
            ['addressLine1' => 'foo'], [], 'foo'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar'], [], 'foo'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'town' => 'cake'], [], 'foo, cake'
        ];
        yield [
            [
                'addressLine1' => 'foo',
                'addressLine2' => 'bar',
                'addressLine3' => 'cake',
                'town' => 'fourth'
            ],
            [],
            'foo, fourth'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
            ['addressFields' => ['addressLine1', 'addressLine2']],
            'foo, bar'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
            ['addressFields' => 'FULL'],
            'foo, bar, cake'
        ];
        yield "BRIEF with postCode" => [
            [
                'addressLine1' => 'foo',
                'addressLine2' => 'bar',
                'addressLine3' => 'cake',
                'addressLine4' => 'baz',
                'town' => 'spam',
                'postcode' => 'eggs',
                'countryCode' => 'ham',
            ],
            ['addressFields' => 'BRIEF'],
            'foo, spam, eggs'
        ];
        yield "BRIEF with blank postCode" => [
            [
                'addressLine1' => 'foo',
                'addressLine2' => 'bar',
                'addressLine3' => 'cake',
                'addressLine4' => 'baz',
                'town' => 'spam',
                'postcode' => '',
                'countryCode' => 'ham',
            ],
            ['addressFields' => 'BRIEF'],
            'foo, spam'
        ];
        yield "BRIEF without postCode" => [
            [
                'addressLine1' => 'foo',
                'addressLine2' => 'bar',
                'addressLine3' => 'cake',
                'addressLine4' => 'baz',
                'town' => 'spam',
                'countryCode' => 'ham',
            ],
            ['addressFields' => 'BRIEF'],
            'foo, spam'
        ];
        yield [
            [
                'address' => [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'town' => 'fourth'
                ]
            ],
            [
                'name' => 'address'
            ],
            'foo, fourth'
        ];
    }

    /**
     * Test the format method with nested keys
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('AddressFormatter')]
    public function testFormatWithNestedKeys(): void
    {
        $this->dataHelper->shouldReceive('fetchNestedData')
            ->with(['foo' => 'bar'], 'bar->baz')
            ->andReturn(['addressLine1' => 'address 1'])
            ->once();

        $data = [
            'foo' => 'bar'
        ];
        $columns = [
            'name' => 'bar->baz'
        ];
        $this->assertEquals('address 1', $this->sut->format($data, $columns));
    }
}
