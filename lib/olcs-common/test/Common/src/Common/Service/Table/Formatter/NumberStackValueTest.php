<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\NumberStackValue;
use Mockery as m;

/**
 * NumberStackValue formatter test
 */
class NumberStackValueTest extends \PHPUnit\Framework\TestCase
{
    protected $stackHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->stackHelper = m::mock(StackHelperService::class);
        $this->sut = new NumberStackValue($this->stackHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormatWithoutStack(): void
    {
        $this->expectException('\InvalidArgumentException');
        $data = [];
        $column = [];

        $this->sut->format($data, $column);
    }

    public function testWithThousandFormatter(): void
    {
        $data = [
            'foo' => [
                'bar' => [
                    'cake' => 12300
                ]
            ]
        ];
        $column = [
            'stack' => 'foo->bar->cake'
        ];
        $expected = '12,300';

        $this->stackHelper->shouldReceive('getStackValue')->once()->with($data, ['foo', 'bar', 'cake'])->andReturn(12300);
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }
}
