<?php

namespace CommonTest\Form\Element;

use Common\Form\Element\DynamicSelect;
use Common\Service\Data\PluginManager;
use Common\Service\Data\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class DynamicSelectTest
 * @package CommonTest\Form\Element
 */
class DynamicSelectTest extends TestCase
{
    private $sut;

    private $mockRefDataService;

    private $pluginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockRefDataService = $this->createMock(RefData::class);
        $this->pluginManager = m::mock(PluginManager::class);
        $this->pluginManager->shouldReceive('get')->with(RefData::class)->andReturn($this->mockRefDataService);

        $this->sut = new DynamicSelect($this->pluginManager, 'name', []);
    }

    public function testSetOptions(): void
    {
        $this->sut->setOptions(['context' => 'testing', 'use_groups' => true, 'other_option' => true, 'label' => 'Testing']);

        $this->assertEquals('testing', $this->sut->getContext());
        $this->assertTrue($this->sut->useGroups());
        $this->assertTrue($this->sut->otherOption());
        $this->assertEquals('Testing', $this->sut->getLabel());
    }

    public function testBcSetOptions(): void
    {
        $this->sut->setOptions(['category' => 'testing']);

        $this->assertEquals('testing', $this->sut->getContext());
    }

    public function testGetValueOptions(): void
    {
        $this->mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with($this->equalTo('category'), $this->equalTo(false))
            ->willReturn(['key' => 'value']);

        $this->sut->setDataService($this->mockRefDataService);
        $this->sut->setContext('category');

        $this->assertEquals(['key' => 'value'], $this->sut->getValueOptions());

        //check that the values are only fetched once
        $this->sut->getValueOptions();
    }

    public function testGetValueOptionsWithOtherOption(): void
    {
        $this->mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with($this->equalTo('category'), $this->equalTo(false))
            ->willReturn(['key' => 'value']);

        $this->sut->setOtherOption(true);
        $this->sut->setDataService($this->mockRefDataService);
        $this->sut->setContext('category');

        $this->assertEquals(['key' => 'value', 'other' => 'Other'], $this->sut->getValueOptions());

        //check that the values are only fetched once
        $this->sut->getValueOptions();
    }

    public function testGetValueOptionsWithExclude(): void
    {
        $this->mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with($this->equalTo('category'), $this->equalTo(false))
            ->willReturn(['key' => 'value', 'exclude' => 'me', 'one_more' => 'one more value']);

        $this->sut->setExclude(['exclude']);
        $this->sut->setDataService($this->mockRefDataService);
        $this->sut->setContext('category');

        $this->assertEquals(['key' => 'value', 'one_more' => 'one more value'], $this->sut->getValueOptions());

        //check that the values are only fetched once
        $this->sut->getValueOptions();
    }

    public function testGetValueOptionsWithEmptyOption(): void
    {
        $this->mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with($this->equalTo('category'), $this->equalTo(false))
            ->willReturn(['key' => 'value']);

        $this->sut->setOtherOption(false);
        $this->sut->setEmptyOption('choose one');
        $this->sut->setContext('category');

        $this->assertEquals(['key' => 'value'], $this->sut->getValueOptions());

        // empty option does not get returned from getValueOptions,
        // it's appended in the view helper - @see Laminas\Form\View\Helper\FormSelect::render
        $this->assertEquals('choose one', $this->sut->getEmptyOption());
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider provideSetValue
     */
    public function testSetValue($value, $expected, $multiple = false): void
    {
        $this->sut->setAttribute('multiple', $multiple);
        $this->sut->setValue($value);

        $this->assertEquals($expected, $this->sut->getValue());
    }

    /**
     * @return ((string|string[])[]|null|string|true)[][]
     *
     * @psalm-return list{list{'test', 'test'}, list{list{'test', 'test2'}, list{'test', 'test2'}}, list{array{id: 'test', desc: 'Test Item'}, 'test'}, list{array<never, never>, null}, list{list{array{id: 'test', desc: 'Test Item'}, list{'test2'}}, list{'test', list{'test2'}}, true}, list{list{array{id: 'test', desc: 'Test Item'}, array{id: 'test2', desc: 'Test Item'}}, list{'test', 'test2'}, true}}
     */
    public function provideSetValue(): array
    {
        return [
            ['test', 'test'],
            [[0 => 'test', 1 => 'test2'], [0 => 'test', 1 => 'test2']],
            [['id' => 'test', 'desc' => 'Test Item'], 'test'],
            [[], null],
            [[['id' => 'test', 'desc' => 'Test Item'], [0 => 'test2']], ['test', [0 => 'test2']], true],
            [[['id' => 'test', 'desc' => 'Test Item'], ['id' => 'test2', 'desc' => 'Test Item']], ['test', 'test2'], true]
        ];
    }

    public function testGetDataServiceThrows(): void
    {
        $serviceName = 'testListService';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Class ' . $serviceName . ' does not implement \Common\Service\Data\ListDataInterface'
        );

        $mockService = $this->createMock('\StdClass');
        $this->pluginManager->expects('get')->with($serviceName)->andReturn($mockService);
        $this->sut->setServiceName($serviceName);
        $this->assertEquals($mockService, $this->sut->getDataService());
    }

    public function testAddValueOption(): void
    {
        $original = [
            1 => 2,
            2 => 3
        ];

        $additional = [
            3 => 4
        ];

        $this->sut->setValueOptions($original);
        $this->sut->addValueOption($additional);

        $this->assertEquals($this->sut->getValueOptions(), array_merge($original, $additional));
    }

    public function testExtraOption(): void
    {
        $this->mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with('category', false)
            ->willReturn(['foo' => 'bar']);

        $this->sut->setExtraOption(['an' => 'option']);
        $this->sut->setContext('category');

        $this->assertSame(['an' => 'option', 'foo' => 'bar'], $this->sut->getValueOptions());
    }

    public function testExtraSetOption(): void
    {
        $this->sut->setOptions(['extra_option' => ['an' => 'option']]);
        $this->assertSame(['an' => 'option'], $this->sut->getExtraOption());
    }
}
