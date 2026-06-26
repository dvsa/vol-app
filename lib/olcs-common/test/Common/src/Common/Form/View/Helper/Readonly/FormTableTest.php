<?php

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\View\Helper\Readonly\FormTable;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class FormTableTest
 * @package CommonTest\Form\View\Helper\Readonly
 */
class FormTableTest extends TestCase
{
    /**
     * @dataProvider provideTestInvoke
     * @param $element
     * @param $expected
     */
    public function testInvoke($element, $expected): void
    {

        $mockView = new \Laminas\View\Renderer\PhpRenderer();

        $sut = new FormTable();

        $sut->setView($mockView);

        $expected ??= $sut;
        $this->assertEquals($expected, $sut($element));
    }

    /**
     * @return (m\LegacyMockInterface&m\MockInterface&\Common\Form\Elements\Types\Table|m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface|m\LegacyMockInterface&m\MockInterface&\Laminas\Form\Element\Select|null|string)[][]
     *
     * @psalm-return list{list{null, null}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface, ''}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface, ''}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\ElementInterface, ''}, list{m\LegacyMockInterface&m\MockInterface&\Laminas\Form\Element\Select, ''}, list{m\LegacyMockInterface&m\MockInterface&\Common\Form\Elements\Types\Table, '<table></table>'}}
     */
    public function provideTestInvoke(): array
    {
        //need tests for Select, TextArea

        $mockHidden = m::mock(\Laminas\Form\ElementInterface::class);
        $mockHidden->shouldReceive('getAttribute')->with('type')->andReturn('hidden');

        $mockRemoveIfReadOnly = m::mock(\Laminas\Form\ElementInterface::class);
        $mockRemoveIfReadOnly->shouldReceive('getAttribute')->with('type')->andReturnNull();
        $mockRemoveIfReadOnly->shouldReceive('getOption')->with('remove_if_readonly')->andReturn(true);

        $mockText = m::mock(\Laminas\Form\ElementInterface::class);
        $mockText->shouldReceive('getAttribute')->with('type')->andReturn('textarea');
        $mockText->shouldReceive('getLabel')->andReturn('Label');
        $mockText->shouldReceive('getValue')->andReturn('Value');
        $mockText->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull();

        $mockSelect = m::mock(\Laminas\Form\Element\Select::class);
        $mockSelect->shouldReceive('getAttribute')->with('type')->andReturn('select');
        $mockSelect->shouldReceive('getLabel')->andReturn('Label');
        $mockSelect->shouldReceive('getValue')->andReturn('Value');
        $mockSelect->shouldReceive('getLabelOption')->andReturn(false);
        $mockSelect->shouldReceive('getAttribute')->andReturn(false);
        $mockSelect->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull();

        $columns = [
            0 => [
                'name' => 'foo',
            ],
            1 => [
                'name' => 'checkbox',
                'type' => 'Checkbox'
            ]
        ];
        $newColumns = [
            0 => [
                'name' => 'foo',
            ]
        ];

        $mockTableBuilder = m::mock(\Common\Service\Table\TableBuilder::class);
        $mockTableBuilder->shouldReceive('setDisabled')->with(true);
        $mockTableBuilder->shouldReceive('getColumns')->andReturn($columns);
        $mockTableBuilder->shouldReceive('setColumns')->with($newColumns);

        $mockTable = m::mock(\Common\Form\Elements\Types\Table::class);
        $mockTable->shouldReceive('getTable')->andReturn($mockTableBuilder);
        $mockTable->shouldReceive('render')->andReturn('<table></table>');

        return [
            [null, null],
            [$mockHidden, ''],
            [$mockRemoveIfReadOnly, ''],
            [$mockText, ''],
            [$mockSelect, ''],
            [$mockTable, '<table></table>'],
        ];
    }
}
