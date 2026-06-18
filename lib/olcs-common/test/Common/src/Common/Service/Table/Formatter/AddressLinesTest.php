<?php

/**
 * AddressLines formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

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
class AddressLinesTest extends MockeryTestCase
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
     * @group Formatters
     * @group AddressLinesFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                ['addressLine1' => 'foo'], [], '<p>foo</p>'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar'], [], '<p>foo</p>'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'town' => 'cake'],
                [],
                '<p>foo,<br />cake</p>'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'town' => 'fourth'
                ],
                [],
                '<p>foo,<br />fourth</p>'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
                ['addressFields' => ['addressLine1', 'addressLine2']],
                '<p>foo,<br />bar</p>'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
                ['addressFields' => 'FULL'],
                '<p>foo,<br />bar,<br />cake</p>'
            ],
            [
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
            ]
        ];
    }

    /**
     * Test the format method with nested keys
     *
     * @group Formatters
     * @group AddressLinesFormatter
     */
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
