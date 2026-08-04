<?php

/**
 * AddressLines formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\AddressLines;
use Common\Test\MockeryTestCase;
use Mockery as m;

/**
 * AddressLines formatter test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class AddressLinesTest extends MockeryTestCase
{
    public $sut;
    protected $dataHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataHelper = m::mock(DataHelperService::class);
        $this->sut =  new AddressLines($this->dataHelper);
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
    #[\PHPUnit\Framework\Attributes\Group('AddressLinesFormatter')]
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
            ['addressLine1' => 'foo'], [], '<p>foo</p>'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar'], [], '<p>foo</p>'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'town' => 'cake'],
            [],
            '<p>foo,<br />cake</p>'
        ];
        yield [
            [
                'addressLine1' => 'foo',
                'addressLine2' => 'bar',
                'addressLine3' => 'cake',
                'town' => 'fourth'
            ],
            [],
            '<p>foo,<br />fourth</p>'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
            ['addressFields' => ['addressLine1', 'addressLine2']],
            '<p>foo,<br />bar</p>'
        ];
        yield [
            ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
            ['addressFields' => 'FULL'],
            '<p>foo,<br />bar,<br />cake</p>'
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
            '<p>foo,<br />fourth</p>'
        ];
    }

    /**
     * Test the format method with nested keys
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('AddressLinesFormatter')]
    public function testFormatWithNestedKeys(): void
    {
        $this->dataHelper->shouldReceive('fetchNestedData')
            ->with(['foo' => 'bar'], 'bar->baz')
            ->once()
            ->andReturn(['addressLine1' => 'address 1']);

        $data = [
            'foo' => 'bar'
        ];
        $columns = [
            'name' => 'bar->baz'
        ];

        $this->assertEquals('<p>address 1</p>', $this->sut->format($data, $columns));
    }
}
