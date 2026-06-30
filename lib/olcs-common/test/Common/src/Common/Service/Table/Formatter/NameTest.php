<?php

/**
 * Name formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\Name;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Name formatter test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class NameTest extends MockeryTestCase
{
    protected $dataHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->dataHelper = m::mock(DataHelperService::class);
        $this->sut = new Name($this->dataHelper);
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
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new Name(new DataHelperService()))->format($data, []));
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
                [
                    'forename' => 'A',
                    'familyName' => 'Person',
                    'title' => [
                        'description' => 'Mr'
                    ]
                ],
                'Mr A Person'
            ],
            [
                [
                    'forename' => 'A',
                    'familyName' => 'Person',
                ],
                'A Person'
            ]
        ];
    }

    public function testFormatNestedData(): void
    {
        $data = [
            'foo' => [
                'forename' => 'John',
                'familyName' => 'Smith',
            ]
        ];
        $this->assertEquals('John Smith', (new Name(new DataHelperService()))->format($data, ['name' => 'foo']));
    }

    public function testEscapedName(): void
    {
        $data = [
            'foo' => [
                'forename' => 'John"',
                'familyName' => 'Smith',
            ]
        ];
        $this->assertEquals('John&quot; Smith', (new Name(new DataHelperService()))->format($data, ['name' => 'foo']));
    }

    public function testFormatDeepNestedData(): void
    {
        $data = [
            'foo' => [
                'name' => [
                    'forename' => 'John',
                    'familyName' => 'Smith',
                ]
            ]
        ];

        $this->dataHelper->shouldReceive('fetchNestedData')
            ->with($data, 'foo->name')
            ->andReturn($data['foo']['name']);

        $this->assertEquals('John Smith', $this->sut->format($data, ['name' => 'foo->name']));
    }
}
