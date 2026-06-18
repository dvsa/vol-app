<?php

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\Address;
use Mockery as m;

/**
 * Address formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressTest extends \PHPUnit\Framework\TestCase
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
     * @group Formatters
     * @group AddressFormatter
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
                ['addressLine1' => 'foo'], [], 'foo'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar'], [], 'foo'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'town' => 'cake'], [], 'foo, cake'
            ],
            [
                [
                    'addressLine1' => 'foo',
                    'addressLine2' => 'bar',
                    'addressLine3' => 'cake',
                    'town' => 'fourth'
                ],
                [],
                'foo, fourth'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
                ['addressFields' => ['addressLine1', 'addressLine2']],
                'foo, bar'
            ],
            [
                ['addressLine1' => 'foo', 'addressLine2' => 'bar', 'addressLine3' => 'cake'],
                ['addressFields' => 'FULL'],
                'foo, bar, cake'
            ],
            "BRIEF with postCode" => [
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
            ],
            "BRIEF with blank postCode" => [
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
            ],
            "BRIEF without postCode" => [
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
                'foo, fourth'
            ]
        ];
    }

    /**
     * Test the format method with nested keys
     *
     * @group Formatters
     * @group AddressFormatter
     */
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
