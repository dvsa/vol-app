<?php

declare(strict_types=1);

namespace CommonTest\Form\Element;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Service\Data\PluginManager;
use Common\Service\Data\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DynamicMultiCheckboxTest extends MockeryTestCase
{
    private $pluginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->pluginManager = m::mock(PluginManager::class);
    }

    public function testSetOptions(): void
    {
        $sut =  new DynamicMultiCheckbox($this->pluginManager);
        $sut->setOptions(['context' => 'testing', 'use_groups' => true, 'label' => 'Testing']);

        $this->assertEquals('testing', $sut->getContext());
        $this->assertTrue($sut->useGroups());
        $this->assertEquals('Testing', $sut->getLabel());
    }

    public function testBcSetOptions(): void
    {
        $sut =  new DynamicMultiCheckbox($this->pluginManager);
        $sut->setOptions(['category' => 'testing']);

        $this->assertEquals('testing', $sut->getContext());
    }

    public function testGetValueOptions(): void
    {
        $mockRefDataService = $this->createMock(RefData::class);
        $mockRefDataService
            ->expects($this->once())
            ->method('fetchListOptions')
            ->with($this->equalTo('category'), $this->equalTo(false))
            ->willReturn(['key' => 'value']);

        $sut = new DynamicMultiCheckbox($this->pluginManager);
        $sut->setDataService($mockRefDataService);
        $sut->setContext('category');

        $this->assertEquals(['key' => 'value'], $sut->getValueOptions());

        //check that the values are only fetched once
        $sut->getValueOptions();
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider provideSetValue
     */
    public function testSetValue($value, $expected): void
    {
        $sut = new DynamicMultiCheckbox($this->pluginManager);
        $sut->setValue($value);

        $this->assertEquals($expected, $sut->getValue());
    }

    /**
     * @return (string|string[])[][]
     *
     * @psalm-return list{list{'test', 'test'}, list{list{'test', 'test2'}, list{'test', 'test2'}}, list{array{id: 'test', desc: 'Test Item'}, 'test'}}
     */
    public function provideSetValue(): array
    {
        return [
            ['test', 'test'],
            [[0 => 'test', 1 => 'test2'], [0 => 'test', 1 => 'test2']],
            [['id' => 'test', 'desc' => 'Test Item'], 'test']
        ];
    }

    public function testGetDataService(): void
    {
        $serviceName = 'testListService';

        $mockService = $this->createMock(\Common\Service\Data\ListDataInterface::class);

        $this->pluginManager->expects('get')->with($serviceName)->andReturn($mockService);
        $sut =  new DynamicMultiCheckbox($this->pluginManager);
        $sut->setServiceName($serviceName);
        $this->assertEquals($mockService, $sut->getDataService());
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
        $sut =  new DynamicMultiCheckbox($this->pluginManager);
        $sut->setOptions(['service_name' => $serviceName]);
        $sut->getDataService();
    }
}
