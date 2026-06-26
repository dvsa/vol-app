<?php

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\View\Helper\Readonly\FormSelect;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class FormSelectTest
 * @package CommonTest\Form\View\Helper\Readonly
 */
class FormSelectTest extends TestCase
{
    /**
     * @param $element
     * @param $expected
     * @dataProvider provideTestInvoke
     */
    public function testInvoke($element, $expected): void
    {
        $sut =  new FormSelect();
        $expected ??= $sut;
        $this->assertEquals($expected, $sut($element));
    }

    /**
     * @return (m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface|m\LegacyMockInterface&m\MockInterface&\Laminas\Form\Element\Select|null|string)[][]
     *
     * @psalm-return list{list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\Element\Select, 'Val 1, Val 2'}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\Element\Select, 'Val 3'}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface, ''}, list{null, null}}
     */
    public function provideTestInvoke(): array
    {
        $valueOptions = [
            'group1' => [
                'options' => [
                    ['value' => 'val1', 'label' => 'Val 1'],
                    'val3' => 'Val 3'
                ]
            ],
            'val2' => 'Val 2'
        ];

        $mockMultiple = m::mock(\Laminas\Form\Element\Select::class);
        $mockMultiple->shouldReceive('getAttribute')->with('multiple')->andReturn(true);
        $mockMultiple->shouldReceive('getValueOptions')->andReturn($valueOptions);
        $mockMultiple->shouldReceive('getValue')->andReturn(['val1', 'val2']);

        $mockSingle = m::mock(\Laminas\Form\Element\Select::class);
        $mockSingle->shouldReceive('getAttribute')->with('multiple')->andReturn(false);
        $mockSingle->shouldReceive('getValueOptions')->andReturn($valueOptions);
        $mockSingle->shouldReceive('getValue')->andReturn('val3');

        return [
            [$mockMultiple, 'Val 1, Val 2'],
            [$mockSingle, 'Val 3'],
            [m::mock(\Laminas\Form\ElementInterface::class), ''],
            [null, null]
        ];
    }
}
